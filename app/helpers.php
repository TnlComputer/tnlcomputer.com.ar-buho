<?php

use Illuminate\Support\Facades\Route;

if (! function_exists('money')) {
    /** Pesos sin decimales: "$ 1.234.567" en español, "ARS 1,234,567" en inglés. */
    function money(float|int|string|null $value): string
    {
        return app()->getLocale() === 'en'
            ? 'ARS '.number_format((float) $value, 0, '.', ',')
            : '$ '.number_format((float) $value, 0, ',', '.');
    }
}

if (! function_exists('whatsapp_url')) {
    function whatsapp_url(?string $text = null): string
    {
        $url = 'https://wa.me/'.config('tnl.whatsapp');

        return $text ? $url.'?text='.rawurlencode($text) : $url;
    }
}

if (! function_exists('is_en')) {
    function is_en(): bool
    {
        return app()->getLocale() === 'en';
    }
}

if (! function_exists('lroute')) {
    /** route() del sitio público en el idioma actual (las rutas en inglés se llaman "en.*"). */
    function lroute(string $name, mixed $parameters = [], bool $absolute = true): string
    {
        return route(is_en() ? "en.{$name}" : $name, $parameters, $absolute);
    }
}

if (! function_exists('route_is')) {
    /** request()->routeIs() que ignora el idioma. */
    function route_is(string ...$patterns): bool
    {
        $all = array_merge($patterns, array_map(fn ($p) => "en.{$p}", $patterns));

        return request()->routeIs(...$all);
    }
}

if (! function_exists('locale_url')) {
    /** La misma página en otro idioma (para el selector y los <link hreflang>). */
    function locale_url(string $locale): string
    {
        $route = Route::current();
        $name = $route?->getName();

        if (! $name || str_starts_with($name, 'admin.')) {
            return $locale === 'en' ? url('/en') : url('/');
        }

        $base = preg_replace('/^en\./', '', $name);
        $target = $locale === 'en' ? "en.{$base}" : $base;
        $url = Route::has($target) ? route($target, $route->parameters()) : url($locale === 'en' ? '/en' : '/');
        $query = request()->getQueryString();

        return $query ? "{$url}?{$query}" : $url;
    }
}
