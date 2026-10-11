<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Collection;

/** Carrito guardado en la sesión: [product_id => cantidad]. */
class Cart
{
    private const KEY = 'cart';

    public const MAX_QTY = 99;

    public function raw(): array
    {
        return session(self::KEY, []);
    }

    public function count(): int
    {
        return array_sum($this->raw());
    }

    public function add(Product $product, int $qty = 1): void
    {
        $cart = $this->raw();
        $cart[$product->id] = min(self::MAX_QTY, ($cart[$product->id] ?? 0) + max(1, $qty));
        session([self::KEY => $cart]);
    }

    public function set(Product $product, int $qty): void
    {
        $cart = $this->raw();
        if ($qty <= 0) {
            unset($cart[$product->id]);
        } else {
            $cart[$product->id] = min(self::MAX_QTY, $qty);
        }
        session([self::KEY => $cart]);
    }

    public function clear(): void
    {
        session()->forget(self::KEY);
    }

    /** Líneas con el producto actualizado desde la base (precio vigente). */
    public function lines(): Collection
    {
        $cart = $this->raw();
        if (! $cart) {
            return collect();
        }

        return Product::with('category')->visible()->whereIn('id', array_keys($cart))->get()
            ->map(fn (Product $p) => (object) [
                'product' => $p,
                'qty' => $cart[$p->id],
                'subtotal' => $p->price * $cart[$p->id],
            ]);
    }

    public function total(Collection $lines): float
    {
        return (float) $lines->sum('subtotal');
    }
}
