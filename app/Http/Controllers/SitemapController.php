<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;

/** sitemap.xml para Google: páginas, rubros y productos visibles, en castellano y en inglés. */
class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = [];
        $add = function (string $route, mixed $params = []) use (&$urls) {
            $urls[] = [route($route, $params), route("en.{$route}", $params)];
        };

        foreach (['home', 'shop.index', 'hosting', 'design', 'service', 'portfolio', 'contact'] as $route) {
            $add($route);
        }

        Category::where('active', true)
            ->whereHas('products', fn ($q) => $q->visible())
            ->orderBy('name')
            ->each(fn ($category) => $add('shop.category', $category));

        Product::visible()->orderBy('code')->select('code')
            ->each(fn ($product) => $add('shop.show', $product));

        // La declaración va fuera de la vista: con short_open_tag activo (como en el hosting) PHP toma "<?xml" como código.
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".view('sitemap', ['urls' => $urls])->render();

        return response($xml)->header('Content-Type', 'application/xml; charset=utf-8');
    }
}
