<?php

namespace App\Providers;

use App\Http\Controllers\InstallController;
use App\Support\Cart;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(Cart::class);

        // Primera instalación sin SSH: si falta APP_KEY se genera y se guarda en el .env antes de que
        // las cookies la necesiten (si no, todo el sitio, incluido /instalar, daría error 500).
        // Solo mientras el instalador está habilitado: un sitio ya instalado nunca escribe su .env.
        $env = base_path('.env');
        if (blank(config('app.key')) && InstallController::enabled()
            && ! $this->app->runningUnitTests() && is_file($env) && is_writable($env)) {
            $key = 'base64:'.base64_encode(Encrypter::generateKey(config('app.cipher')));
            $content = file_get_contents($env);
            $content = preg_match('/^APP_KEY=.*$/m', $content)
                ? preg_replace('/^APP_KEY=.*$/m', 'APP_KEY='.$key, $content)
                : "APP_KEY={$key}\n".$content;
            file_put_contents($env, $content);
            config(['app.key' => $key]);
        }
    }

    public function boot(): void
    {
        RateLimiter::for('forms', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
        // Por IP y también por cuenta: probar contraseñas desde muchas IPs distintas tampoco sirve.
        RateLimiter::for('login', fn (Request $request) => [
            Limit::perMinute(5)->by($request->ip()),
            Limit::perMinutes(15, 10)->by('email:'.mb_strtolower((string) $request->input('email'))),
        ]);
        // Códigos de 6 dígitos: pocos intentos por sesión y por IP.
        RateLimiter::for('two-factor', fn (Request $request) => [
            Limit::perMinute(5)->by($request->session()->getId()),
            Limit::perMinutes(15, 15)->by($request->ip()),
        ]);

        View::composer('layouts.site', fn ($view) => $view->with('cartCount', app(Cart::class)->count()));
    }
}
