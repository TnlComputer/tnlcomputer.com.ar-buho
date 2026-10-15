<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PaymentSync;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $status = array_key_exists($request->query('estado'), Order::STATUSES) ? $request->query('estado') : null;

        return view('admin.orders.index', [
            'orders' => Order::withCount('items')
                ->when($status, fn ($q) => $q->where('status', $status))
                ->when($request->query('q'), fn ($q, $term) => $q->where(fn ($w) => $w
                    ->where('customer_name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('id', ltrim(preg_replace('/\D/', '', $term), '0') ?: 0)))
                ->latest()->paginate(30)->withQueryString(),
            'status' => $status,
            'counts' => Order::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function show(Order $order): View
    {
        return view('admin.orders.show', ['order' => $order->load('items.product', 'payments')]);
    }

    /**
     * Cambios manuales: pagado (cobro con link manual), preparado/enviado, entregado, cancelado;
     * código de seguimiento y link de pago manual.
     */
    public function update(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(['paid', 'refunded', 'ship', 'deliver', 'cancel', 'tracking', 'payment_link'])],
            'tracking_code' => ['nullable', 'string', 'max:100'],
            'payment_link' => ['nullable', 'url:https', 'max:500'],
            // Número de operación de Mercado Pago del cobro con link manual: sirve para ubicarlo y reintegrarlo.
            'operation_id' => ['exclude_unless:action,paid', 'required', 'regex:/^\d{6,20}$/', Rule::unique('payments', 'provider_id')],
        ], [
            'operation_id.required' => 'Ingresá el número de operación del cobro en Mercado Pago.',
            'operation_id.regex' => 'El número de operación tiene solo números (lo ves en el detalle del cobro en Mercado Pago).',
            'operation_id.unique' => 'Esa operación ya está asociada a un pedido.',
        ]);

        match ($data['action']) {
            'paid' => $this->markPaid($order, $data['operation_id']),
            'refunded' => $this->markRefunded($order),
            'ship' => $order->update(['status' => Order::SHIPPED, 'shipped_at' => now(), 'tracking_code' => $data['tracking_code'] ?? $order->tracking_code]),
            'deliver' => $order->update(['status' => Order::DELIVERED, 'delivered_at' => now()]),
            'cancel' => $order->update(['status' => Order::CANCELLED]),
            'tracking' => $order->update(['tracking_code' => $data['tracking_code']]),
            'payment_link' => $order->update(['payment_link' => $data['payment_link'] ?? null]),
        };

        return back()->with('status', 'Pedido actualizado.');
    }

    /** Cobro con link manual: queda registrado con su número de operación, como un pago más del pedido. */
    private function markPaid(Order $order, string $operationId): void
    {
        DB::transaction(function () use ($order, $operationId) {
            $order->payments()->create([
                'provider' => 'manual', 'provider_id' => $operationId, 'status' => 'approved',
                'amount' => $order->total, 'approved_at' => now(),
            ]);
            $order->update(['status' => Order::PAID, 'paid_at' => $order->paid_at ?? now()]);
        });
    }

    /** Reintegro hecho en Mercado Pago de un cobro manual (los automáticos se actualizan solos). */
    private function markRefunded(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order->payments()->where('provider', 'manual')->where('status', 'approved')
                ->update(['status' => 'refunded', 'amount_refunded' => DB::raw('amount')]);
            $order->update(['status' => Order::REFUNDED]);
        });
    }

    /** Vuelve a consultar en Mercado Pago los pagos automáticos del pedido (por si se perdió un aviso). */
    public function refresh(Order $order, PaymentSync $sync): RedirectResponse
    {
        foreach ($order->payments->where('provider', 'mercadopago') as $payment) {
            $sync->sync($payment->provider_id, $order);
        }

        return back()->with('status', 'Estado consultado en Mercado Pago.');
    }
}
