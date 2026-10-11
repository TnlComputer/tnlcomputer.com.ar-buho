<?php

namespace App\Http\Controllers;

use App\Services\MercadoPago;
use App\Services\PaymentSync;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Avisos de Mercado Pago (Webhooks). Solo se usa el ID del pago: el resto se consulta a la API.
 * Responde 200 rápido; si algo falla devuelve 500 para que Mercado Pago reintente.
 */
class MercadoPagoWebhookController extends Controller
{
    public function __invoke(Request $request, MercadoPago $mp, PaymentSync $sync): Response
    {
        $type = $request->input('type', $request->query('type', $request->query('topic')));
        // PHP convierte "data.id" de la query en "data_id".
        $paymentId = (string) ($request->query('data_id') ?? $request->input('data.id') ?? $request->query('id') ?? '');

        if ($type !== 'payment' || $paymentId === '') {
            return response('ignored', 200);
        }

        if (! $mp->validSignature($request, $paymentId)) {
            Log::warning('Webhook de Mercado Pago con firma inválida', ['payment' => $paymentId, 'ip' => $request->ip()]);

            return response('invalid signature', 401);
        }

        try {
            $sync->sync($paymentId);
        } catch (\Throwable $e) {
            Log::error("Error procesando el webhook del pago {$paymentId}", ['error' => $e->getMessage()]);

            return response('error', 500);
        }

        return response('ok', 200);
    }
}
