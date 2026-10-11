<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    private const SORTS = [
        'nombre' => ['name', 'asc'],
        'menor-precio' => ['price', 'asc'],
        'mayor-precio' => ['price', 'desc'],
        'nuevos' => ['updated_at', 'desc'],
    ];

    public function index(Request $request, ?Category $category = null): View
    {
        $sort = array_key_exists($request->query('orden'), self::SORTS) ? $request->query('orden') : 'nombre';
        [$column, $direction] = self::SORTS[$sort];

        $products = Product::with('category')->visible()
            ->when($category, fn ($q) => $q->where('category_id', $category->id))
            ->search($request->query('q'))
            ->orderBy($column, $direction)
            ->paginate(24)
            ->withQueryString();

        $categories = Category::where('active', true)
            ->whereHas('products', fn ($q) => $q->visible())
            ->withCount(['products' => fn ($q) => $q->visible()])
            ->orderBy('name')
            ->get();

        return view('shop.index', [
            'products' => $products,
            'groups' => $this->groups($categories),
            'category' => $category,
            'sort' => $sort,
        ]);
    }

    /** Agrupa los rubros en familias (config tnl.shop_groups) para el menú lateral. */
    private function groups($categories): array
    {
        $groups = [];
        $assigned = [];

        foreach (config('tnl.shop_groups') as $name => [$icon, $slugs]) {
            $items = $categories->filter(fn ($c) => in_array($c->slug, $slugs, true))
                ->sortBy(fn ($c) => array_search($c->slug, $slugs, true))->values();
            if ($items->isNotEmpty()) {
                $groups[] = ['name' => $name, 'icon' => $icon, 'items' => $items, 'total' => $items->sum('products_count')];
                $assigned = array_merge($assigned, $items->pluck('id')->all());
            }
        }

        $others = $categories->whereNotIn('id', $assigned)->values();
        if ($others->isNotEmpty()) {
            $groups[] = ['name' => 'Otros', 'icon' => 'box', 'items' => $others, 'total' => $others->sum('products_count')];
        }

        return $groups;
    }

    public function show(Product $product): View
    {
        abort_unless($product->active, 404);

        $related = Product::visible()
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->inRandomOrder()
            ->take(4)
            ->get();

        return view('shop.show', compact('product', 'related'));
    }
}
