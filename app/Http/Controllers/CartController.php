<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private Cart $cart) {}

    public function index(): View
    {
        $lines = $this->cart->lines();

        return view('shop.cart', ['lines' => $lines, 'total' => $this->cart->total($lines)]);
    }

    public function add(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->active && $product->stock !== Product::SIN_STOCK, 404);

        $this->cart->add($product, (int) $request->input('qty', 1));

        return back()->with('cart_added', $product->name)
            ->with('ga_add_to_cart', $product->gaItem(max(1, (int) $request->input('qty', 1))));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->cart->set($product, (int) $request->input('qty', 0));

        return redirect(lroute('cart.index'));
    }
}
