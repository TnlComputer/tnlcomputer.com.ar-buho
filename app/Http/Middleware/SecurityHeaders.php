<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/** Cabeceras de seguridad para todas las respuestas. */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        // Nonce por respuesta: solo corren los scripts inline que lo llevan (los de nuestras vistas y los de Vite).
        $nonce = Vite::useCspNonce();

        $response = $next($request);

        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=()',
            'Cross-Origin-Opener-Policy' => 'same-origin',
        ];

        if ($request->isSecure()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }

        $mode = config('tnl.csp');
        if (in_array($mode, ['report', 'enforce'], true) && str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
            $name = $mode === 'enforce' ? 'Content-Security-Policy' : 'Content-Security-Policy-Report-Only';
            $headers[$name] = $this->policy($nonce);
        }

        foreach ($headers as $name => $value) {
            if (! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        // Las páginas de pedidos y del panel tienen datos personales: que no queden en cachés intermedias.
        if ($request->is('admin*', 'pedido/*', 'en/pedido/*', 'checkout', 'en/checkout')) {
            $response->headers->set('Cache-Control', 'no-store, private');
        }

        return $response;
    }

    /**
     * Qué puede cargar el sitio: lo propio, Google Analytics y (como destino de formularios) Mercado Pago,
     * porque el checkout redirige ahí después del POST. Los estilos inline quedan permitidos (atributos style
     * de algunas vistas); los scripts no.
     */
    private function policy(string $nonce): string
    {
        $google = 'https://*.googletagmanager.com https://*.google-analytics.com https://*.analytics.google.com';
        $vite = app()->isLocal() ? ' http://localhost:* ws://localhost:* http://127.0.0.1:* ws://127.0.0.1:* http://[::1]:* ws://[::1]:*' : '';

        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}' https://www.googletagmanager.com{$vite}",
            "style-src 'self' 'unsafe-inline'{$vite}",
            "img-src 'self' data: {$google}",
            "font-src 'self' data:{$vite}",
            "connect-src 'self' {$google}{$vite}",
            "frame-src 'none'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self' https://*.mercadopago.com.ar https://*.mercadopago.com https://*.mercadolibre.com",
            "frame-ancestors 'self'",
            'report-uri '.route('csp.report', absolute: false),
        ]);
    }
}
