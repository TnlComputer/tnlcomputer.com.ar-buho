<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Integración con Mercado Pago Checkout Pro (API REST).
 *
 * Seguridad: los datos de tarjeta los carga el cliente en Mercado Pago, nunca pasan por este servidor.
 * El estado de un pago SIEMPRE se consulta a la API de Mercado Pago; nunca se confía en lo que llega por
 * la URL de retorno o en el cuerpo del webhook.
 */
class MercadoPago
{
    private const API = 'https://api.mercadopago.com';

    public function configured(): bool
    {
        return filled(config('services.mercadopago.access_token'));
    }

    private function http(): PendingRequest
    {
        if (! $this->configured()) {
            throw new RuntimeException('Mercado Pago no está configurado (falta MP_ACCESS_TOKEN).');
        }

        return Http::baseUrl(self::API)
            ->withToken(config('services.mercadopago.access_token'))
            ->acceptJson()
            ->timeout(20)
            ->retry(2, 500, throw: false);
    }

    /** Crea la preferencia de pago y devuelve la URL de Mercado Pago a la que se redirige al cliente. */
    public function createPreference(Order $order): string
    {
        $order->loadMissing('items');
        $config = config('services.mercadopago');

        $items = $order->items->map(fn ($item) => [
            'id' => $item->code,
            'title' => mb_substr($item->name, 0, 250),
            'quantity' => (int) $item->qty,
            'unit_price' => round((float) $item->unit_price, 2),
            'currency_id' => 'ARS',
        ])->values()->all();

        // El envío va como un ítem más: el cliente ve en Mercado Pago exactamente lo mismo que en el sitio.
        if ((float) $order->shipping_cost > 0) {
            $items[] = [
                'id' => 'ENVIO',
                'title' => __('Envío').' — '.$order->shipping_zone_name,
                'quantity' => 1,
                'unit_price' => round((float) $order->shipping_cost, 2),
                'currency_id' => 'ARS',
            ];
        }

        $orderUrl = $this->localizedOrderUrl($order);

        $payload = [
            'items' => $items,
            'external_reference' => $order->token,
            'payer' => array_filter([
                'name' => $order->customer_name,
                'email' => $order->email,
                'identification' => $order->dni ? ['type' => 'DNI', 'number' => preg_replace('/\D/', '', $order->dni)] : null,
            ]),
            'back_urls' => ['success' => $orderUrl, 'pending' => $orderUrl, 'failure' => $orderUrl],
            'auto_return' => 'approved',
            'notification_url' => route('webhooks.mercadopago'),
            'statement_descriptor' => $config['statement_descriptor'],
            'payment_methods' => [
                'installments' => $config['max_installments'],
                'excluded_payment_types' => array_map(fn ($id) => ['id' => trim($id)], $config['excluded_payment_types']),
            ],
            'expires' => true,
            'expiration_date_from' => now()->toIso8601String(),
            'expiration_date_to' => now()->addHours($config['expiration_hours'])->toIso8601String(),
            'metadata' => ['order_number' => $order->number()],
        ];

        $response = $this->http()
            ->withHeaders(['X-Idempotency-Key' => $order->token.'-'.now()->timestamp])
            ->post('/checkout/preferences', $payload);

        if (! $response->successful() || ! $response->json('init_point')) {
            throw new RuntimeException('Mercado Pago rechazó la preferencia: '.$response->status().' '.$response->body());
        }

        $order->update(['mp_preference_id' => $response->json('id')]);

        return $response->json('init_point');
    }

    /** Consulta un pago en la API. Devuelve null si no existe. */
    public function getPayment(string $paymentId): ?array
    {
        if (! preg_match('/^\d{1,20}$/', $paymentId)) {
            return null;
        }

        $response = $this->http()->get("/v1/payments/{$paymentId}");

        if ($response->status() === 404) {
            return null;
        }
        if (! $response->successful()) {
            throw new RuntimeException("No se pudo consultar el pago {$paymentId}: ".$response->status());
        }

        return $response->json();
    }

    /**
     * Valida la firma x-signature del webhook (HMAC-SHA256 con la clave secreta de Webhooks).
     * En producción la clave es obligatoria: sin MP_WEBHOOK_SECRET se rechazan todos los avisos (los pagos igual
     * se actualizan al volver de Mercado Pago o con "Volver a consultar"). En local/pruebas se aceptan sin firma.
     */
    public function validSignature(Request $request, string $dataId): bool
    {
        $secret = config('services.mercadopago.webhook_secret');
        if (blank($secret)) {
            return ! app()->isProduction();
        }

        $parts = [];
        foreach (explode(',', (string) $request->header('x-signature')) as $piece) {
            [$key, $value] = array_pad(explode('=', trim($piece), 2), 2, null);
            $parts[$key] = $value;
        }
        if (empty($parts['ts']) || empty($parts['v1'])) {
            return false;
        }

        // Rechaza avisos viejos (más de 1 hora) para evitar repeticiones.
        $ts = (int) $parts['ts'];
        $ts = $ts > 1e12 ? intdiv($ts, 1000) : $ts;
        if (abs(time() - $ts) > 3600) {
            return false;
        }

        $id = ctype_alnum($dataId) ? strtolower($dataId) : $dataId;
        $manifest = "id:{$id};";
        if ($requestId = $request->header('x-request-id')) {
            $manifest .= "request-id:{$requestId};";
        }
        $manifest .= "ts:{$parts['ts']};";

        return hash_equals(hash_hmac('sha256', $manifest, $secret), $parts['v1']);
    }

    private function localizedOrderUrl(Order $order): string
    {
        return route($order->locale === 'en' ? 'en.orders.show' : 'orders.show', $order);
    }
}
