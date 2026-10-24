<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Publica en la página de Facebook, en Instagram (cuenta profesional, con su propio token), en Threads, en X y en TikTok.
 * Las credenciales van en el .env (ver .env.example); una red sin credenciales simplemente no se usa.
 */
class SocialMedia
{
    public const NETWORKS = ['facebook', 'instagram', 'threads', 'x', 'tiktok'];

    private const GRAPH = 'https://graph.facebook.com';

    private const X_API = 'https://api.x.com/2';

    /** API de Instagram con inicio de sesión de Instagram: no hace falta vincular la cuenta a la página de Facebook. */
    private const INSTAGRAM_API = 'https://graph.instagram.com';

    /** Token de Instagram vigente: dura 60 días y se renueva solo cada semana, como el de Threads. */
    private const INSTAGRAM_TOKEN_FILE = 'app/instagram-token.json';

    private const THREADS_API = 'https://graph.threads.net/v1.0';

    /** Token de Threads vigente: dura 60 días y se renueva solo cada semana. */
    private const THREADS_TOKEN_FILE = 'app/threads-token.json';

    private const TIKTOK_API = 'https://open.tiktokapis.com/v2';

    /** Token de TikTok vigente (el de acceso dura 24 horas y cada renovación puede traer otro de renovación). */
    private const TIKTOK_TOKEN_FILE = 'app/tiktok-token.json';

    /** Carpeta (dentro de public/) de las imágenes armadas para las publicaciones. */
    private const CARD_DIR = 'uploads/social';

    public function configured(string $network): bool
    {
        $c = config('services.social');

        return match ($network) {
            'facebook' => filled($c['facebook_page_id']) && filled($c['facebook_page_token']),
            'instagram' => filled($c['instagram_token']),
            'x' => filled($c['x_api_key']) && filled($c['x_api_secret']) && filled($c['x_access_token']) && filled($c['x_access_secret']),
            'threads' => filled($c['threads_token']),
            'tiktok' => filled($c['tiktok_client_key']) && filled($c['tiktok_client_secret']) && filled($c['tiktok_refresh_token']),
            default => false,
        };
    }

    /** Foto en la página de Facebook (se sube el archivo, no hace falta que esté publicado). Devuelve el id de la publicación. */
    public function facebook(string $imagePath, string $text): string
    {
        $c = config('services.social');
        $r = Http::baseUrl(self::GRAPH.'/'.config('services.social.graph_version'))->acceptJson()->timeout(60)
            ->attach('source', file_get_contents($imagePath), basename($imagePath))->post("{$c['facebook_page_id']}/photos", [
                'message' => $text,
                'access_token' => $c['facebook_page_token'],
            ]);

        return $this->graphId($r, 'post_id', 'id');
    }

    /** Foto en Instagram: se crea el contenedor, se espera a que Meta baje la imagen y se publica. */
    public function instagram(string $imageUrl, string $text): string
    {
        $token = $this->instagramToken();
        $http = Http::baseUrl(self::INSTAGRAM_API.'/'.config('services.social.graph_version'))->asForm()->acceptJson()->timeout(60);

        $container = $this->graphId($http->post('me/media', [
            'image_url' => $imageUrl,
            'caption' => $text,
            'access_token' => $token,
        ]), 'id');

        for ($i = 0; $i < 10; $i++) {
            $status = $http->get($container, ['fields' => 'status_code', 'access_token' => $token])->json('status_code');
            if ($status === 'FINISHED') {
                break;
            }
            if ($status === 'ERROR' || $status === 'EXPIRED') {
                throw new RuntimeException("Instagram no pudo procesar la imagen ({$status}).");
            }
            sleep(3);
        }

        return $this->graphId($http->post('me/media_publish', [
            'creation_id' => $container,
            'access_token' => $token,
        ]), 'id');
    }

    /** Token de larga duración de Instagram: se renueva cada 7 días (vence a los 60); si en el .env se carga uno nuevo, se usa ese. */
    private function instagramToken(): string
    {
        $file = storage_path(self::INSTAGRAM_TOKEN_FILE);
        $env = config('services.social.instagram_token');
        $saved = is_file($file) ? (array) json_decode((string) file_get_contents($file), true) : [];
        if (($saved['env'] ?? null) !== $env) {
            $saved = ['env' => $env, 'access_token' => $env, 'refreshed_at' => time()];
        }

        if ($saved['refreshed_at'] < time() - 7 * 86400) {
            $r = Http::acceptJson()->timeout(30)->get(self::INSTAGRAM_API.'/refresh_access_token', [
                'grant_type' => 'ig_refresh_token',
                'access_token' => $saved['access_token'],
            ]);
            if ($r->json('access_token')) {
                $saved['access_token'] = $r->json('access_token');
                $saved['refreshed_at'] = time();
            } else {
                report(new RuntimeException('Instagram: no se pudo renovar el token: '.$r->body()));
            }
        }
        file_put_contents($file, json_encode($saved));

        return $saved['access_token'];
    }

    /** Post en X con la imagen. Si la subida de la imagen falla, sale igual con el link (X arma la vista previa). */
    public function x(string $imagePath, string $text): string
    {
        $mediaId = null;
        $upload = Http::withHeaders(['Authorization' => $this->xAuth('POST', self::X_API.'/media/upload')])
            ->timeout(60)
            ->attach('media', file_get_contents($imagePath), basename($imagePath))
            ->post(self::X_API.'/media/upload', ['media_category' => 'tweet_image', 'media_type' => 'image/jpeg']);
        if ($upload->successful()) {
            $mediaId = $upload->json('data.id') ?? $upload->json('media_id_string');
        } else {
            report(new RuntimeException('X: no se pudo subir la imagen: '.$upload->body()));
        }

        $r = Http::withHeaders(['Authorization' => $this->xAuth('POST', self::X_API.'/tweets')])
            ->acceptJson()->timeout(30)
            ->post(self::X_API.'/tweets', array_filter([
                'text' => $text,
                'media' => $mediaId ? ['media_ids' => [(string) $mediaId]] : null,
            ]));

        return $r->json('data.id') ?? throw new RuntimeException('X: '.$r->status().' '.$r->body());
    }

    /** Post con foto en Threads: igual que Instagram, contenedor y después publicación. */
    public function threads(string $imageUrl, string $text): string
    {
        ['access_token' => $token, 'user_id' => $user] = $this->threadsToken();
        $http = Http::baseUrl(self::THREADS_API)->asForm()->acceptJson()->timeout(60);

        $container = $this->graphId($http->post("{$user}/threads", [
            'media_type' => 'IMAGE',
            'image_url' => $imageUrl,
            'text' => $text,
            'access_token' => $token,
        ]), 'id');

        for ($i = 0; $i < 10; $i++) {
            $status = $http->get($container, ['fields' => 'status,error_message', 'access_token' => $token]);
            if ($status->json('status') === 'FINISHED') {
                break;
            }
            if (in_array($status->json('status'), ['ERROR', 'EXPIRED'], true)) {
                throw new RuntimeException('Threads no pudo procesar la imagen: '.$status->json('error_message'));
            }
            sleep(3);
        }

        return $this->graphId($http->post("{$user}/threads_publish", [
            'creation_id' => $container,
            'access_token' => $token,
        ]), 'id');
    }

    /**
     * Token de larga duración de Threads y el id de la cuenta. El token se renueva cada 7 días (vence a los 60)
     * y se guarda en storage/; si en el .env se carga uno nuevo, se usa ese.
     *
     * @return array{access_token: string, user_id: string}
     */
    private function threadsToken(): array
    {
        $file = storage_path(self::THREADS_TOKEN_FILE);
        $env = config('services.social.threads_token');
        $saved = is_file($file) ? (array) json_decode((string) file_get_contents($file), true) : [];
        if (($saved['env'] ?? null) !== $env) {
            $saved = ['env' => $env, 'access_token' => $env, 'refreshed_at' => time()];
        }

        if ($saved['refreshed_at'] < time() - 7 * 86400) {
            $r = Http::acceptJson()->timeout(30)->get('https://graph.threads.net/refresh_access_token', [
                'grant_type' => 'th_refresh_token',
                'access_token' => $saved['access_token'],
            ]);
            if ($r->json('access_token')) {
                $saved['access_token'] = $r->json('access_token');
                $saved['refreshed_at'] = time();
            } else {
                report(new RuntimeException('Threads: no se pudo renovar el token: '.$r->body()));
            }
        }

        $saved['user_id'] ??= $this->graphId(
            Http::acceptJson()->timeout(30)->get(self::THREADS_API.'/me', ['fields' => 'id', 'access_token' => $saved['access_token']]),
            'id'
        );
        file_put_contents($file, json_encode($saved));

        return ['access_token' => $saved['access_token'], 'user_id' => $saved['user_id']];
    }

    /** Foto en TikTok (publicación directa; TikTok baja la imagen de la URL). Devuelve el id de la publicación. */
    public function tiktok(string $imageUrl, string $title, string $text): string
    {
        $r = Http::withToken($this->tiktokToken())->acceptJson()->timeout(30)
            ->post(self::TIKTOK_API.'/post/publish/content/init/', [
                'post_info' => [
                    'title' => Str::limit($title, 85),
                    'description' => $text,
                    'privacy_level' => config('services.social.tiktok_privacy'),
                    'disable_comment' => false,
                    'auto_add_music' => true,
                ],
                'source_info' => ['source' => 'PULL_FROM_URL', 'photo_cover_index' => 0, 'photo_images' => [$imageUrl]],
                'post_mode' => 'DIRECT_POST',
                'media_type' => 'PHOTO',
            ]);

        return $r->json('data.publish_id') ?? throw new RuntimeException('TikTok: '.($r->json('error.message') ?: $r->status().' '.$r->body()));
    }

    /** Pide un token a TikTok, con el código de autorización (tnl:tiktok-conectar) o con el de renovación. */
    public function tiktokRequest(array $form): array
    {
        $c = config('services.social');
        $r = Http::asForm()->acceptJson()->timeout(30)->post(self::TIKTOK_API.'/oauth/token/', [
            'client_key' => $c['tiktok_client_key'],
            'client_secret' => $c['tiktok_client_secret'],
            ...$form,
        ]);
        if (! $r->json('access_token')) {
            throw new RuntimeException('TikTok: '.($r->json('error_description') ?: $r->body()));
        }

        return $r->json();
    }

    /**
     * Token de acceso de TikTok: se renueva con el de renovación y se guarda en storage/. Si en el .env se carga un
     * TIKTOK_REFRESH_TOKEN nuevo (porque se volvió a conectar la cuenta), se usa ese.
     */
    private function tiktokToken(): string
    {
        $file = storage_path(self::TIKTOK_TOKEN_FILE);
        $env = config('services.social.tiktok_refresh_token');
        $saved = is_file($file) ? (array) json_decode((string) file_get_contents($file), true) : [];
        if (($saved['env'] ?? null) !== $env) {
            $saved = [];
        }
        if (($saved['expires_at'] ?? 0) > time() + 300) {
            return $saved['access_token'];
        }

        $token = $this->tiktokRequest(['grant_type' => 'refresh_token', 'refresh_token' => $saved['refresh_token'] ?? $env]);
        file_put_contents($file, json_encode([
            'env' => $env,
            'access_token' => $token['access_token'],
            'refresh_token' => $token['refresh_token'] ?? $saved['refresh_token'] ?? $env,
            'expires_at' => time() + (int) ($token['expires_in'] ?? 86400),
        ]));

        return $token['access_token'];
    }

    /**
     * Imagen vertical de 1080×1350 (4:5, la que más lugar ocupa en el feed de Instagram) en JPG, porque Instagram
     * no acepta PNG ni WebP: fondo con los colores de la marca, la foto del producto en un recuadro blanco, el nombre,
     * el precio destacado y una línea con envíos y el sitio. Se dibuja al doble de tamaño y se achica al final para que
     * los bordes redondeados y los textos queden suaves. Devuelve la ruta relativa a public/.
     */
    public function card(Product $product): string
    {
        $photo = @imagecreatefromstring((string) file_get_contents(public_path($product->image)));
        if (! $photo) {
            throw new RuntimeException("No se pudo leer la imagen de {$product->code} ({$product->image}).");
        }
        // Muchas fotos del proveedor traen mucho margen blanco o transparente: se pasa a fondo blanco y se recorta
        // para que el producto llene el recuadro.
        $flat = imagecreatetruecolor(imagesx($photo), imagesy($photo));
        imagefill($flat, 0, 0, imagecolorallocate($flat, 255, 255, 255));
        imagecopy($flat, $photo, 0, 0, 0, 0, imagesx($photo), imagesy($photo));
        $photo = $flat;
        if ($trimmed = imagecropauto($photo, IMG_CROP_THRESHOLD, 0.1, 0xFFFFFF)) {
            $photo = $trimmed;
        }

        $k = 2;
        [$W, $H] = [1080 * $k, 1350 * $k];
        $img = imagecreatetruecolor($W, $H);
        $color = fn (int $hex, int $alpha = 0) => imagecolorallocatealpha($img, $hex >> 16 & 255, $hex >> 8 & 255, $hex & 255, $alpha);
        $display = resource_path('fonts/SpaceGrotesk-Bold.ttf');
        $semi = resource_path('fonts/Inter-SemiBold.ttf');
        $bold = resource_path('fonts/Inter-ExtraBold.ttf');

        // Fondo: degradé vertical del violeta de la marca a casi negro, con un resplandor arriba a la derecha.
        for ($y = 0; $y < $H; $y++) {
            $t = $y / $H;
            imageline($img, 0, $y, $W, $y, imagecolorallocate($img, (int) (0x8A + (0x24 - 0x8A) * $t), (int) (0x28 + (0x0B - 0x28) * $t), (int) (0x74 + (0x22 - 0x74) * $t)));
        }
        for ($r = 900 * $k; $r > 0; $r -= 30 * $k) {
            imagefilledellipse($img, 960 * $k, 120 * $k, $r * 2, $r * 2, $color(0xC946AE, 122));
        }

        // Arriba: logo, nombre de la tienda y el rubro.
        imagefilledellipse($img, 102 * $k, 96 * $k, 84 * $k, 84 * $k, $color(0xFFFFFF));
        if ($logo = @imagecreatefrompng(public_path('logo192.png'))) {
            imagecopyresampled($img, $logo, 72 * $k, 64 * $k, 0, 0, 60 * $k, 64 * $k, imagesx($logo), imagesy($logo));
        }
        imagettftext($img, 30 * $k, 0, 160 * $k, 110 * $k, $color(0xFFFFFF), $display, 'TNL Computer');
        if ($category = $product->category?->name) {
            $label = mb_strtoupper($category);
            $box = imagettfbbox(18 * $k, 0, $semi, $label);
            $pw = $box[2] - $box[0] + 48 * $k;
            // Blanco al 20 % sobre el fondo, mezclado a mano: con transparencia las uniones del redondeo se notan.
            $bg = imagecolorat($img, $W - 60 * $k - (int) ($pw / 2), 96 * $k);
            $mix = fn (int $shift) => (int) round(($bg >> $shift & 255) * 0.8 + 255 * 0.2);
            $this->roundedRect($img, $W - 60 * $k - $pw, 72 * $k, $W - 60 * $k, 120 * $k, 24 * $k, $color($mix(16) << 16 | $mix(8) << 8 | $mix(0)));
            imagettftext($img, 18 * $k, 0, $W - 60 * $k - $pw + 24 * $k, 104 * $k, $color(0xFFFFFF), $semi, $label);
        }

        // Recuadro blanco con la foto: entra sin deformarse; las chicas se agrandan como mucho al doble.
        [$bx1, $by1, $bx2, $by2] = [60 * $k, 170 * $k, $W - 60 * $k, 870 * $k];
        $this->roundedRect($img, $bx1, $by1, $bx2, $by2, 44 * $k, $color(0xFFFFFF));
        [$w, $h] = [imagesx($photo), imagesy($photo)];
        $pad = 60 * $k;
        $scale = min(($bx2 - $bx1 - 2 * $pad) / $w, ($by2 - $by1 - 2 * $pad) / $h, 2 * $k);
        [$dw, $dh] = [(int) round($w * $scale), (int) round($h * $scale)];
        imagecopyresampled($img, $photo, (int) (($bx1 + $bx2 - $dw) / 2), (int) (($by1 + $by2 - $dh) / 2), 0, 0, $dw, $dh, $w, $h);

        // Nombre (hasta 2 renglones) y precio en una etiqueta amarilla.
        $y = 960 * $k;
        foreach ($this->wrap($this->displayName($product), $display, 44 * $k, $W - 120 * $k, 2) as $line) {
            imagettftext($img, 44 * $k, 0, 60 * $k, $y, $color(0xFFFFFF), $display, $line);
            $y += 60 * $k;
        }
        $price = money($product->price);
        $box = imagettfbbox(76 * $k, 0, $bold, $price);
        $tw = $box[2] - $box[0];
        $py = 1080 * $k;
        $this->roundedRect($img, 60 * $k, $py, 60 * $k + $tw + 64 * $k, $py + 130 * $k, 30 * $k, $color(0xFFD23F));
        imagettftext($img, 76 * $k, 0, 92 * $k - $box[0], $py + 103 * $k, $color(0x24081F), $bold, $price);
        // Al lado del precio, achicado si el precio es largo.
        $x = 60 * $k + $tw + 100 * $k;
        $note = 22 * $k;
        while ($note > 15 * $k && ($b = imagettfbbox($note, 0, $semi, 'Pagá con Mercado Pago'))[2] - $b[0] > $W - 60 * $k - $x) {
            $note -= $k;
        }
        imagettftext($img, $note, 0, $x, $py + 58 * $k, $color(0xFFFFFF), $semi, 'Comprá online');
        imagettftext($img, $note, 0, $x, $py + 96 * $k, $color(0xFFFFFF, 40), $semi, 'Pagá con Mercado Pago');

        // Pie: envíos y el sitio.
        imageline($img, 60 * $k, 1250 * $k, $W - 60 * $k, 1250 * $k, $color(0xFFFFFF, 105));
        imagettftext($img, 24 * $k, 0, 60 * $k, 1305 * $k, $color(0xFFFFFF, 30), $semi, 'Envíos a todo el país  ·  Garantía');
        $site = 'tnlcomputer.com.ar';
        $box = imagettfbbox(26 * $k, 0, $display, $site);
        imagettftext($img, 26 * $k, 0, $W - 60 * $k - ($box[2] - $box[0]), 1306 * $k, $color(0xFFD23F), $display, $site);

        $card = imagecreatetruecolor(1080, 1350);
        imagecopyresampled($card, $img, 0, 0, 0, 0, 1080, 1350, $W, $H);

        $dir = public_path(self::CARD_DIR);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $file = self::CARD_DIR.'/'.Str::slug($product->code ?: (string) $product->id).'.jpg';
        imagejpeg($card, public_path($file), 90);

        return $file;
    }

    /** Nombre para mostrar: sin el "[código]" del proveedor adelante ni el "(código de barras)" al final. */
    public function displayName(Product $product): string
    {
        return preg_replace(['/^\[[^\]]*\]\s*/', '/\s*\(\d{8,14}\)\s*$/'], '', $product->name);
    }

    /** Rectángulo relleno con las esquinas redondeadas. */
    private function roundedRect(\GdImage $img, int $x1, int $y1, int $x2, int $y2, int $r, int $color): void
    {
        imagefilledrectangle($img, $x1 + $r + 1, $y1, $x2 - $r - 1, $y2, $color);
        if ($y2 - $y1 > 2 * $r + 2) {
            imagefilledrectangle($img, $x1, $y1 + $r + 1, $x1 + $r, $y2 - $r - 1, $color);
            imagefilledrectangle($img, $x2 - $r, $y1 + $r + 1, $x2, $y2 - $r - 1, $color);
        }
        // Un cuarto de círculo por esquina, sin pisar los rectángulos: así los colores semitransparentes no se suman.
        foreach ([[$x1 + $r, $y1 + $r, 180], [$x2 - $r, $y1 + $r, 270], [$x2 - $r, $y2 - $r, 0], [$x1 + $r, $y2 - $r, 90]] as [$cx, $cy, $from]) {
            imagefilledarc($img, $cx, $cy, $r * 2, $r * 2, $from, $from + 90, $color, IMG_ARC_PIE);
        }
    }

    /**
     * Parte el texto en renglones que entren en $width; si sobra texto, el último renglón termina en "…".
     *
     * @return list<string>
     */
    private function wrap(string $text, string $font, int $size, int $width, int $maxLines): array
    {
        $fits = fn (string $s) => (($b = imagettfbbox($size, 0, $font, $s))[2] - $b[0]) <= $width;
        $lines = [''];
        foreach (preg_split('/\s+/', trim($text)) as $word) {
            $try = trim(end($lines).' '.$word);
            if ($fits($try)) {
                $lines[array_key_last($lines)] = $try;
            } else {
                $lines[] = $word;
            }
        }
        if (count($lines) > $maxLines) {
            $lines = array_slice($lines, 0, $maxLines);
            $last = $lines[$maxLines - 1].'…';
            while (! $fits($last) && mb_strlen($last) > 1) {
                $last = rtrim(mb_substr($last, 0, -2)).'…';
            }
            $lines[$maxLines - 1] = $last;
        }

        return $lines;
    }

    /** Primer id que venga en la respuesta de Meta, o una excepción con el mensaje de error. */
    private function graphId(Response $r, string ...$keys): string
    {
        foreach ($keys as $key) {
            if ($r->successful() && filled($r->json($key))) {
                return (string) $r->json($key);
            }
        }

        throw new RuntimeException('Meta: '.($r->json('error.message') ?? $r->status().' '.$r->body()));
    }

    /** Cabecera OAuth 1.0a (contexto de usuario) de X. Los cuerpos JSON y multipart no entran en la firma. */
    private function xAuth(string $method, string $url): string
    {
        $c = config('services.social');
        $oauth = [
            'oauth_consumer_key' => $c['x_api_key'],
            'oauth_nonce' => Str::random(32),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => (string) time(),
            'oauth_token' => $c['x_access_token'],
            'oauth_version' => '1.0',
        ];
        $params = implode('&', array_map(fn ($k, $v) => rawurlencode($k).'='.rawurlencode($v), array_keys($oauth), $oauth));
        $base = strtoupper($method).'&'.rawurlencode($url).'&'.rawurlencode($params);
        $oauth['oauth_signature'] = base64_encode(hash_hmac('sha1', $base, rawurlencode($c['x_api_secret']).'&'.rawurlencode($c['x_access_secret']), true));

        return 'OAuth '.implode(', ', array_map(fn ($k, $v) => $k.'="'.rawurlencode($v).'"', array_keys($oauth), $oauth));
    }
}
