<?php

namespace App\Console\Commands;

use App\Services\SocialMedia;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Conecta la cuenta de TikTok una sola vez y devuelve el TIKTOK_REFRESH_TOKEN para el .env del servidor.
 * Se corre en la PC (el hosting no tiene SSH), con TIKTOK_CLIENT_KEY y TIKTOK_CLIENT_SECRET en el .env local.
 *
 * Uso:  php artisan tnl:tiktok-conectar
 */
class ConnectTikTok extends Command
{
    protected $signature = 'tnl:tiktok-conectar';

    protected $description = 'Autoriza la cuenta de TikTok y muestra el token de renovación para el .env.';

    public function handle(SocialMedia $social): int
    {
        $c = config('services.social');
        if (blank($c['tiktok_client_key']) || blank($c['tiktok_client_secret'])) {
            $this->error('Faltan TIKTOK_CLIENT_KEY y TIKTOK_CLIENT_SECRET en el .env.');

            return self::FAILURE;
        }

        $url = 'https://www.tiktok.com/v2/auth/authorize/?'.http_build_query([
            'client_key' => $c['tiktok_client_key'],
            'scope' => 'user.info.basic,video.publish',
            'response_type' => 'code',
            'redirect_uri' => $c['tiktok_redirect_uri'],
            'state' => Str::random(16),
        ]);
        $this->line('1. Abrí este link con la cuenta de TikTok de la empresa y autorizá la app:');
        $this->newLine();
        $this->line("   <comment>{$url}</comment>");
        $this->newLine();
        $this->line('2. TikTok vuelve al sitio con "?code=..." en la dirección. Copiá esa dirección completa.');

        $answer = trim((string) $this->ask('Dirección (o solo el código)'));
        parse_str((string) parse_url($answer, PHP_URL_QUERY), $query);
        $code = $query['code'] ?? $answer;

        $token = $social->tiktokRequest([
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $c['tiktok_redirect_uri'],
        ]);

        $days = (int) (($token['refresh_expires_in'] ?? 0) / 86400);
        $this->newLine();
        $this->info('Listo. Agregá esta línea al .env del servidor:');
        $this->line("TIKTOK_REFRESH_TOKEN={$token['refresh_token']}");
        if ($days) {
            $this->line("Vence en {$days} días: después hay que volver a correr este comando.");
        }

        return self::SUCCESS;
    }
}
