<?php

namespace App\Services;

use App\Mail\OrderPaid;
use App\Mail\OrderPaidAdmin;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/** Trae un pago de Mercado Pago, lo asocia a su pedido y actualiza el estado. Idempotente. */
class PaymentSync
{
    public function __construct(private MercadoPago $mp) {}

    /** @return Order|null el pedido afectado */
    public function sync(string $paymentId, ?Order $expected = null): ?Order
    {
        $data = $this->mp->getPayment($paymentId);
        if (! $data) {
            return null;
        }

        $order = Order::where('token', (string) ($data['external_reference'] ?? ''))->first();

        // El pago tiene que ser de este pedido (evita que alguien "use" un pago de otra compra).
        if (! $order || ($expected && ! $expected->is($order))) {
            Log::warning('Pago de Mercado Pago sin pedido que coincida', ['payment' => $paymentId]);

            return null;
        }
        if (($data['currency_id'] ?? null) !== 'ARS') {
            Log::warning('Pago en moneda inesperada', ['payment' => $paymentId, 'currency' => $data['currency_id'] ?? null]);

            return $order;
        }

        $wasPaid = $order->status === Order::PAID;

        DB::transaction(function () use ($order, $data) {
            Order::whereKey($order->id)->lockForUpdate()->first();

            Payment::updateOrCreate(
                ['provider' => 'mercadopago', 'provider_id' => (string) $data['id']],
                [
                    'order_id' => $order->id,
                    'status' => $data['status'],
                    'status_detail' => $data['status_detail'] ?? null,
                    'payment_type' => $data['payment_type_id'] ?? null,
                    'payment_method' => $data['payment_method_id'] ?? null,
                    'card_last_four' => $data['card']['last_four_digits'] ?? null,
                    'installments' => $data['installments'] ?? null,
                    'amount' => $data['transaction_amount'] ?? 0,
                    'amount_refunded' => $data['transaction_amount_refunded'] ?? 0,
                    'approved_at' => isset($data['date_approved']) ? Carbon::parse($data['date_approved']) : null,
                ],
            );

            $order->syncStatusFromPayments();
        });

        if (! $wasPaid && $order->fresh()->status === Order::PAID) {
            $this->notifyPaid($order->fresh());
        }

        return $order->fresh();
    }

    private function notifyPaid(Order $order): void
    {
        try {
            Mail::to($order->email)->locale($order->locale)->send(new OrderPaid($order));
            Mail::to(config('tnl.notify_to'))->locale('es')->send(new OrderPaidAdmin($order));
        } catch (\Throwable $e) {
            Log::error("No se pudieron enviar los mails del pedido {$order->number()}", ['error' => $e->getMessage()]);
        }
    }
}
