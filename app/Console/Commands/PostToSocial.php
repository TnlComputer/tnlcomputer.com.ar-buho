<?php

namespace App\Console\Commands;

use App\Http\Controllers\GoogleFeedController;
use App\Models\Product;
use App\Models\SocialPost;
use App\Services\SocialMedia;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Throwable;

/**
 * Publica un producto (foto, precio y link) en Facebook, Instagram, Threads, X y TikTok. Lo corre el scheduler en los horarios de
 * SOCIAL_TIMES; elige al azar entre los que se pueden comprar y no se publicaron en los últimos SOCIAL_REPEAT_DAYS días.
 *
 * Uso:
 *   php artisan tnl:redes                          # un producto al azar, en todas las redes configuradas
 *   php artisan tnl:redes --producto=40606         # un producto puntual
 *   php artisan tnl:redes --red=facebook --red=x   # solo esas redes
 *   php artisan tnl:redes --dry-run                # arma la imagen y muestra los textos, sin publicar
 */
class PostToSocial extends Command
{
    protected $signature = 'tnl:redes
        {--producto= : Código del producto (por defecto, uno al azar)}
        {--red=*     : facebook, instagram, threads, x o tiktok (por defecto, todas las configuradas)}
        {--dry-run   : Arma la imagen y muestra los textos, sin publicar}';

    protected $description = 'Publica un producto de la tienda en Facebook, Instagram, Threads, X y TikTok.';

    public function handle(SocialMedia $social): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $networks = array_values(array_intersect(SocialMedia::NETWORKS, $this->option('red') ?: SocialMedia::NETWORKS));
        if (! $dryRun) {
            $networks = array_values(array_filter($networks, $social->configured(...)));
        }
        if (! $networks) {
            $this->error('No hay ninguna red configurada: faltan las credenciales en el .env (ver .env.example).');

            return self::FAILURE;
        }

        $product = $this->pickProduct();
        if (! $product) {
            $this->error('No hay ningún producto para publicar.');

            return self::FAILURE;
        }

        $card = $social->card($product);
        $imageUrl = asset($card).'?v='.time();
        $this->info("{$product->code} — {$product->name}");
        $this->line("Imagen: {$imageUrl}");

        $ok = false;
        foreach ($networks as $network) {
            $text = $this->text($product, $network);
            if ($dryRun) {
                $this->newLine();
                $this->line("<comment>[{$network}]</comment>\n{$text}");

                continue;
            }

            try {
                $id = match ($network) {
                    'facebook' => $social->facebook($imageUrl, $text),
                    'instagram' => $social->instagram($imageUrl, $text),
                    'x' => $social->x(public_path($card), $text),
                    'threads' => $social->threads($imageUrl, $text),
                    'tiktok' => $social->tiktok($imageUrl, $product->name, $text),
                };
                SocialPost::create(['product_id' => $product->id, 'network' => $network, 'status' => 'ok', 'remote_id' => $id]);
                $this->line("  {$network}: publicado ({$id})");
                $ok = true;
            } catch (Throwable $e) {
                SocialPost::create(['product_id' => $product->id, 'network' => $network, 'status' => 'error', 'error' => mb_substr($e->getMessage(), 0, 2000)]);
                report($e);
                $this->error("  {$network}: {$e->getMessage()}");
            }
        }

        return $ok || $dryRun ? self::SUCCESS : self::FAILURE;
    }

    /** Producto visible, con foto y precio, que se venda suelto y que no haya salido hace poco. */
    private function pickProduct(): ?Product
    {
        $base = Product::visible()->with('category')
            ->whereNotNull('image')->where('image', '<>', '')->where('price', '>', 0);

        if ($code = $this->option('producto')) {
            return (clone $base)->where('code', $code)->first();
        }

        $recent = SocialPost::where('status', 'ok')
            ->where('created_at', '>=', now()->subDays(config('services.social.repeat_days')))
            ->distinct()->pluck('product_id');

        // Si ya salieron todos, se vuelve a elegir entre todos.
        foreach ([(clone $base)->whereNotIn('id', $recent), $base] as $query) {
            $product = $query->inRandomOrder()->limit(50)->get()
                ->first(fn (Product $p) => ! preg_match(GoogleFeedController::EXCLUDE, $p->name) && is_file(public_path($p->image)));
            if ($product) {
                return $product;
            }
        }

        return null;
    }

    private function text(Product $product, string $network): string
    {
        $url = route('shop.show', $product);
        $price = money($product->price);
        $tags = collect(['TNLComputer', 'Tecnologia', 'Hardware', Str::studly(Str::ascii((string) $product->category?->name))])
            ->filter()->unique()->map(fn ($t) => '#'.preg_replace('/[^A-Za-z0-9]/', '', $t))->implode(' ');

        return match ($network) {
            // Threads acepta hasta 500 caracteres.
            'threads' => Str::limit($product->name, 200)."

{$price}

Compralo online: {$url}

#TNLComputer",
            // En X el link cuenta 23 caracteres, sea cual sea su largo; el total no puede pasar de 280.
            'x' => Str::limit($product->name, 150)." — {$price}\n{$url}\n#TNLComputer",
            // En Instagram y TikTok los links del texto no se pueden tocar.
            'instagram', 'tiktok' => "{$product->name}\n\n{$price}\n\nEncontralo en tnlcomputer.com.ar (código {$product->code}) o consultanos por WhatsApp al "
                .config('tnl.whatsapp_label').".\n\n{$tags}",
            default => "{$product->name}\n\n{$price}\n\nCompralo online: {$url}\nConsultas por WhatsApp: ".whatsapp_url()."\n\n{$tags}",
        };
    }
}
