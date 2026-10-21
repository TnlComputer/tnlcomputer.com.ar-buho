<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Reseñas desde la página del pedido (el token del pedido hace de contraseña, así que solo opina quien compró).
 * Quedan pendientes hasta que se aprueban en el panel.
 */
class ReviewController extends Controller
{
    public function store(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->canReview(), 403);

        $data = $request->validate([
            'reviews' => ['required', 'array'],
            'reviews.*.rating' => ['nullable', 'integer', 'between:1,5'],
            'reviews.*.comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $productIds = $order->items()->whereNotNull('product_id')->pluck('product_id')->all();
        $already = $order->reviews()->pluck('product_id')->all();
        $saved = 0;

        foreach ($data['reviews'] as $productId => $review) {
            $productId = (int) $productId;
            if (empty($review['rating']) || ! in_array($productId, $productIds, true) || in_array($productId, $already, true)) {
                continue;
            }

            $order->reviews()->create([
                'product_id' => $productId,
                'author' => Review::authorFrom($order->customer_name),
                'rating' => (int) $review['rating'],
                'comment' => filled($review['comment'] ?? null) ? trim($review['comment']) : null,
                'ip' => $request->ip(),
            ]);
            $saved++;
        }

        return redirect(lroute('orders.show', $order).'#resenas')->with('review_status', $saved
            ? __('¡Gracias por tu opinión! La publicamos en la tienda en cuanto la revisemos.')
            : __('Elegí cuántas estrellas le das a cada producto para enviar tu opinión.'));
    }
}
