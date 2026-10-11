<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

/**
 * Idioma por URL: /en/... es inglés, el resto español. Es middleware global
 * (no de ruta) para que también las páginas de error salgan en el idioma correcto.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->segment(1) === 'en' ? 'en' : 'es';

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
