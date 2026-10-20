<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Response;

/**
 * Feed de productos para Google Merchant Center (RSS 2.0 con el espacio de nombres g:).
 * Solo productos visibles, con foto y que se puedan comprar sueltos.
 */
class GoogleFeedController extends Controller
{
    /** Productos que no se venden solos (van con una PC o notebook armada) o que no son nuevos. */
    private const EXCLUDE = '/solo para pc armada|pc o notebook armada|para sku notebook|\brma\b/i';

    /** Se suma a la URL de cada imagen; cambiarlo obliga a Google a volver a descargarlas ("Imagen no procesada"). */
    private const IMAGE_VERSION = 2;

    public function __invoke(): Response
    {
        $products = Product::visible()->with('category')
            ->whereNotNull('image')->where('image', '<>', '')->where('price', '>', 0)
            ->orderBy('code')->get()
            ->filter(fn (Product $p) => ! preg_match(self::EXCLUDE, $p->name) && is_file(public_path($p->image)));

        // La declaración va fuera de la vista: con short_open_tag activo (como en el hosting) PHP toma "<?xml" como código.
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".view('google-feed', ['products' => $products, 'imageVersion' => self::IMAGE_VERSION])->render();

        return response($xml)->header('Content-Type', 'application/xml; charset=utf-8');
    }
}
