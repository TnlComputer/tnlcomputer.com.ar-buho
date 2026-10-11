<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PaymentSync;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    /** Cambios manuales de estado: preparado/enviado, entregado, cancelado; y código de seguimiento. */
    public function update(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(['ship', 'deliver', 'cancel', 'tracking'])],
            'tracking_code' => ['nullable', 'string', 'max:100'],
        ]);

        match ($data['action']) {
            'ship' => $order->update(['status' => Order::SHIPPED, 'shipped_at' => now(), 'tracking_code' => $data['tracking_code'] ?? $order->tracking_code]),
            'deliver' => $order->update(['status' => Order::DELIVERED, 'delivered_at' => now()]),
            'cancel' => $order->update(['status' => Order::CANCELLED]),
            'tracking' => $order->update(['tracking_code' => $data['tracking_code']]),
        };

        return back()->with('status', 'Pedido actualizado.');
    }

    /** Vuelve a consultar en Mercado Pago todos los pagos del pedido (por si se perdió un aviso). */
    public function refresh(Order $order, PaymentSync $sync): RedirectResponse
    {
        foreach ($order->payments as $payment) {
            $sync->sync($payment->provider_id, $order);
        }

        return back()->with('status', 'Estado consultado en Mercado Pago.');
    }
}
