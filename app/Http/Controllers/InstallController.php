<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Instalador de un solo uso para hostings sin SSH.
 * Se habilita solo si el .env tiene INSTALL_TOKEN y todavía no existe storage/app/installed.lock.
 */
class InstallController extends Controller
{
    public static function lockFile(): string
    {
        return storage_path('app/installed.lock');
    }

    public static function enabled(): bool
    {
        return filled(env('INSTALL_TOKEN')) && ! is_file(self::lockFile());
    }

    public function show(): View
    {
        abort_unless(self::enabled(), 404);

        return view('install', ['checks' => $this->checks()]);
    }

    public function run(Request $request): View
    {
        abort_unless(self::enabled(), 404);

        $data = $request->validate([
            'token' => ['required', 'string'],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:10', 'confirmed'],
        ]);
        abort_unless(hash_equals((string) env('INSTALL_TOKEN'), $data['token']), 403);

        $log = [];
        $checks = $this->checks();
        if (collect($checks)->contains(fn ($c) => ! $c[1])) {
            return view('install', ['checks' => $checks, 'error' => 'Hay requisitos sin cumplir: revisalos antes de instalar.']);
        }

        try {
            if (blank(config('app.key'))) {
                $key = 'base64:'.base64_encode(Encrypter::generateKey(config('app.cipher')));
                $this->setEnv('APP_KEY', $key);
                config(['app.key' => $key]);
                $log[] = 'APP_KEY generada.';
            }

            Artisan::call('migrate', ['--force' => true]);
            $log[] = 'Tablas creadas.';
            Artisan::call('db:seed', ['--force' => true]);
            $log[] = trim(Artisan::output()) ?: 'Datos cargados.';

            User::updateOrCreate(['email' => $data['email']], ['name' => $data['name'], 'password' => $data['password']]);
            $log[] = "Usuario administrador {$data['email']} listo.";

            Artisan::call('optimize:clear');
            file_put_contents(self::lockFile(), now()->toDateTimeString());
            $log[] = 'Instalador desactivado.';
        } catch (\Throwable $e) {
            return view('install', ['checks' => $checks, 'log' => $log, 'error' => $e->getMessage()]);
        }

        return view('install', ['done' => true, 'log' => $log]);
    }

    /** @return array<int, array{0: string, 1: bool}> */
    private function checks(): array
    {
        $db = true;
        try {
            DB::connection()->getPdo();
        } catch (\Throwable) {
            $db = false;
        }

        return [
            ['PHP 8.2 o superior ('.PHP_VERSION.')', version_compare(PHP_VERSION, '8.2.0', '>=')],
            ...array_map(fn ($ext) => ["Extensión {$ext}", extension_loaded($ext)], ['pdo_mysql', 'mbstring', 'openssl', 'fileinfo', 'curl', 'tokenizer', 'xml']),
            ['storage/ con permiso de escritura', is_writable(storage_path())],
            ['bootstrap/cache/ con permiso de escritura', is_writable(base_path('bootstrap/cache'))],
            ['.env con permiso de escritura', is_writable(base_path('.env'))],
            ['public/uploads/ con permiso de escritura', is_writable(public_path()) || is_writable(public_path('uploads'))],
            ['Conexión a la base de datos ('.config('database.connections.mysql.database').')', $db],
        ];
    }

    private function setEnv(string $key, string $value): void
    {
        $path = base_path('.env');
        $env = file_get_contents($path);
        $line = "{$key}={$value}";
        $env = preg_match("/^{$key}=.*$/m", $env)
            ? preg_replace("/^{$key}=.*$/m", $line, $env)
            : rtrim($env)."\n{$line}\n";
        file_put_contents($path, $env);
    }
}
