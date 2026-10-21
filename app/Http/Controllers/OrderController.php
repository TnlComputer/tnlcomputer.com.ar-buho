<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\MercadoPago;
use App\Services\PaymentSync;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Página pública de seguimiento (el token de 40 caracteres hace de contraseña).
     * Al volver de Mercado Pago trae ?payment_id=...: se verifica contra la API para mostrar el estado al instante,
     * sin esperar el webhook. Lo que diga la URL (status=approved, etc.) se ignora.
     */
    public function show(Request $request, Order $order, PaymentSync $sync): View
    {
        $paymentId = (string) $request->query('payment_id', '');
        if ($paymentId !== '' && $paymentId !== 'null') {
            try {
                $sync->sync($paymentId, $order);
            } catch (\Throwable $e) {
                Log::warning("No se pudo verificar el pago {$paymentId} al volver de Mercado Pago", ['error' => $e->getMessage()]);
            }
            $order->refresh();
        }

        return view('shop.order', [
            'order' => $order->load('items', 'payments', 'reviews'),
            'paymentsEnabled' => app(MercadoPago::class)->configured(),
        ]);
    }

    /** El cliente se arrepiente antes de pagar. Si igual termina un pago en Mercado Pago, el pedido vuelve a pagado. */
    public function cancel(Order $order): RedirectResponse
    {
        abort_unless($order->canCancel(), 403);

        $order->update(['status' => Order::CANCELLED]);

        return redirect(lroute('orders.show', $order));
    }
}
