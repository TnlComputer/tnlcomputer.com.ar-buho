<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Publica en la página de Facebook, en Instagram (cuenta profesional vinculada a esa página) y en X.
 * Las credenciales van en el .env (ver .env.example); una red sin credenciales simplemente no se usa.
 */
class SocialMedia
{
    public const NETWORKS = ['facebook', 'instagram', 'x'];

    private const GRAPH = 'https://graph.facebook.com';

    private const X_API = 'https://api.x.com/2';

    /** Carpeta (dentro de public/) de las imágenes armadas para las publicaciones. */
    private const CARD_DIR = 'uploads/social';

    public function configured(string $network): bool
    {
        $c = config('services.social');

        return match ($network) {
            'facebook' => filled($c['facebook_page_id']) && filled($c['facebook_page_token']),
            'instagram' => filled($c['instagram_user_id']) && filled($c['facebook_page_token']),
            'x' => filled($c['x_api_key']) && filled($c['x_api_secret']) && filled($c['x_access_token']) && filled($c['x_access_secret']),
            default => false,
        };
    }

    /** Foto en la página de Facebook. Devuelve el id de la publicación. */
    public function facebook(string $imageUrl, string $text): string
    {
        $c = config('services.social');
        $r = $this->graph()->post("{$c['facebook_page_id']}/photos", [
            'url' => $imageUrl,
            'message' => $text,
            'access_token' => $c['facebook_page_token'],
        ]);

        return $this->graphId($r, 'post_id', 'id');
    }

    /** Foto en Instagram: se crea el contenedor, se espera a que Meta baje la imagen y se publica. */
    public function instagram(string $imageUrl, string $text): string
    {
        $c = config('services.social');
        $token = $c['facebook_page_token'];

        $container = $this->graphId($this->graph()->post("{$c['instagram_user_id']}/media", [
            'image_url' => $imageUrl,
            'caption' => $text,
            'access_token' => $token,
        ]), 'id');

        for ($i = 0; $i < 10; $i++) {
            $status = $this->graph()->get($container, ['fields' => 'status_code', 'access_token' => $token])->json('status_code');
            if ($status === 'FINISHED') {
                break;
            }
            if ($status === 'ERROR' || $status === 'EXPIRED') {
                throw new RuntimeException("Instagram no pudo procesar la imagen ({$status}).");
            }
            sleep(3);
        }

        return $this->graphId($this->graph()->post("{$c['instagram_user_id']}/media_publish", [
            'creation_id' => $container,
            'access_token' => $token,
        ]), 'id');
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

    /**
     * Imagen cuadrada de 1080×1080 en JPG (Instagram no acepta PNG ni WebP): la foto del producto sobre blanco
     * y una franja con el sitio y el precio. Devuelve la ruta relativa a public/.
     */
    public function card(Product $product): string
    {
        $photo = @imagecreatefromstring((string) file_get_contents(public_path($product->image)));
        if (! $photo) {
            throw new RuntimeException("No se pudo leer la imagen de {$product->code} ({$product->image}).");
        }
        // Muchas fotos del proveedor traen mucho margen blanco o transparente: se pasa a fondo blanco y se recorta
        // para que el producto llene la imagen.
        $flat = imagecreatetruecolor(imagesx($photo), imagesy($photo));
        imagefill($flat, 0, 0, imagecolorallocate($flat, 255, 255, 255));
        imagecopy($flat, $photo, 0, 0, 0, 0, imagesx($photo), imagesy($photo));
        $photo = $flat;
        if ($trimmed = imagecropauto($photo, IMG_CROP_THRESHOLD, 0.1, 0xFFFFFF)) {
            $photo = $trimmed;
        }

        $size = 1080;
        $band = 150;
        $card = imagecreatetruecolor($size, $size);
        imagefill($card, 0, 0, imagecolorallocate($card, 255, 255, 255));

        // La foto entra en el área libre sin deformarse; las chicas se agrandan como mucho al doble.
        [$w, $h] = [imagesx($photo), imagesy($photo)];
        $scale = min(($size - 80) / $w, ($size - $band - 80) / $h, 2);
        [$dw, $dh] = [(int) round($w * $scale), (int) round($h * $scale)];
        imagecopyresampled($card, $photo, (int) (($size - $dw) / 2), (int) (($size - $band - $dh) / 2), 0, 0, $dw, $dh, $w, $h);

        imagefilledrectangle($card, 0, $size - $band, $size, $size, imagecolorallocate($card, 0xA8, 0x33, 0x8F));
        $font = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans-Bold.ttf');
        if (function_exists('imagettftext') && is_file($font)) {
            $white = imagecolorallocate($card, 255, 255, 255);
            $baseline = $size - (int) ($band / 2) + 16;
            imagettftext($card, 30, 0, 50, $baseline, $white, $font, 'tnlcomputer.com.ar');
            $price = money($product->price);
            $box = imagettfbbox(52, 0, $font, $price);
            imagettftext($card, 52, 0, $size - 50 - ($box[2] - $box[0]), $baseline + 10, $white, $font, $price);
        }

        $dir = public_path(self::CARD_DIR);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $file = self::CARD_DIR.'/'.Str::slug($product->code ?: (string) $product->id).'.jpg';
        imagejpeg($card, public_path($file), 88);

        return $file;
    }

    private function graph(): PendingRequest
    {
        return Http::baseUrl(self::GRAPH.'/'.config('services.social.graph_version'))->asForm()->acceptJson()->timeout(60);
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
