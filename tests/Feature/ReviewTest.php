<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    private function deliveredOrder(string $status = Order::DELIVERED): array
    {
        $product = Product::create(['code' => 'K1', 'name' => 'Teclado Redragon Kumara', 'price' => 1000, 'stock' => 'DISPONIBLE']);
        $other = Product::create(['code' => 'M1', 'name' => 'Mouse que no compró', 'price' => 500, 'stock' => 'DISPONIBLE']);
        $order = Order::create(['customer_name' => 'ana maría pérez', 'email' => 'ana@example.com', 'phone' => '1144445555',
            'subtotal' => 1000, 'total' => 1000, 'status' => $status]);
        $order->items()->create(['product_id' => $product->id, 'code' => 'K1', 'name' => $product->name, 'unit_price' => 1000, 'qty' => 1, 'subtotal' => 1000]);

        return [$order, $product, $other];
    }

    public function test_buyer_reviews_a_delivered_order_and_it_waits_for_approval(): void
    {
        [$order, $product, $other] = $this->deliveredOrder();

        $this->get("/pedido/{$order->token}")->assertSee('Calificá lo que compraste');
        $this->post("/pedido/{$order->token}/opinar", ['reviews' => [
            $product->id => ['rating' => 5, 'comment' => ' Excelente, llegó rápido '],
            $other->id => ['rating' => 1, 'comment' => 'No lo compré'],
        ]])->assertRedirect();

        $review = Review::sole();
        $this->assertSame([$product->id, 'Ana P.', 5, 'Excelente, llegó rápido', 'pending'],
            [$review->product_id, $review->author, $review->rating, $review->comment, $review->status]);

        // Pendiente: no se muestra en la ficha.
        $this->get('/tienda/producto/K1')->assertDontSee('Excelente, llegó rápido');

        // Una sola reseña por producto y pedido.
        $this->post("/pedido/{$order->token}/opinar", ['reviews' => [$product->id => ['rating' => 1]]]);
        $this->assertSame(1, Review::count());
    }

    public function test_only_delivered_orders_can_review(): void
    {
        [$order, $product] = $this->deliveredOrder(Order::PAID);

        $this->get("/pedido/{$order->token}")->assertDontSee('Calificá lo que compraste');
        $this->post("/pedido/{$order->token}/opinar", ['reviews' => [$product->id => ['rating' => 5]]])->assertForbidden();
        $this->assertSame(0, Review::count());
    }

    public function test_approved_reviews_show_on_the_product_page_with_structured_data(): void
    {
        [$order, $product] = $this->deliveredOrder();
        $review = $order->reviews()->create(['product_id' => $product->id, 'author' => 'Ana P.', 'rating' => 4, 'comment' => 'Muy cómodo para escribir']);

        $this->actingAs(User::create(['name' => 'A', 'email' => 'a@example.com', 'password' => 'x']));
        $this->get('/admin/resenas')->assertSee('Muy cómodo para escribir');
        $this->put("/admin/resenas/{$review->id}", ['status' => 'approved'])->assertRedirect();
        $this->assertNotNull($review->fresh()->approved_at);

        $this->get('/tienda/producto/K1')->assertOk()
            ->assertSee('Muy cómodo para escribir')
            ->assertSee('Compra verificada')
            ->assertSee('"aggregateRating":{"@type":"AggregateRating","ratingValue":4', false);
    }

    public function test_review_poster_prints_the_google_review_qr(): void
    {
        $this->actingAs(User::create(['name' => 'A', 'email' => 'a@example.com', 'password' => 'x']));

        $this->get('/admin/resenas/cartel')->assertOk()->assertSee('<svg', false)->assertSee(config('tnl.google_review'));
    }

    public function test_author_name_is_first_name_and_last_initial(): void
    {
        $this->assertSame('Ana P.', Review::authorFrom('ana maría pérez'));
        $this->assertSame('Jorge', Review::authorFrom('JORGE'));
    }
}
