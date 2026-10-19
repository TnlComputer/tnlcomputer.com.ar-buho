<?php

namespace Tests\Feature;

use App\Mail\OrderPaid;
use App\Mail\OrderPaidAdmin;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingZone;
use App\Models\User;
use Database\Seeders\ShippingZoneSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ShippingZoneSeeder::class);
        config([
            'services.mercadopago.access_token' => 'TEST-token',
            'services.mercadopago.webhook_secret' => 'secreto',
        ]);
        Mail::fake();
    }

    private function cartWith(int $qty = 2, float $price = 100000): Product
    {
        $product = Product::create(['code' => 'P1', 'name' => 'Placa de video', 'price' => $price, 'stock' => 'DISPONIBLE']);
        $this->post("/carrito/{$product->code}", ['qty' => $qty]);

        return $product;
    }

    private function fakePreference(): void
    {
        Http::fake(['api.mercadopago.com/checkout/preferences' => Http::response(['id' => 'pref-1', 'init_point' => 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=pref-1'])]);
    }

    private function checkoutData(array $extra = []): array
    {
        return $extra + [
            'name' => 'Ana Pérez', 'email' => 'ana@example.com', 'phone' => '11 4444-5555', 'dni' => '30123456',
            'delivery' => 'shipping', 'postal_code' => 'C1425ABC', 'address' => 'Av. Siempreviva 742', 'city' => 'CABA', 'province' => 'CABA',
            'terms' => '1',
        ];
    }

    private function paymentPayload(Order $order, array $extra = []): array
    {
        return $extra + [
            'id' => 111, 'status' => 'approved', 'status_detail' => 'accredited', 'external_reference' => $order->token,
            'currency_id' => 'ARS', 'transaction_amount' => (float) $order->total, 'transaction_amount_refunded' => 0,
            'payment_type_id' => 'credit_card', 'payment_method_id' => 'visa', 'installments' => 3,
            'card' => ['last_four_digits' => '4242'], 'date_approved' => now()->toIso8601String(),
        ];
    }

    public function test_shipping_quote_by_postal_code(): void
    {
        $this->cartWith(1, 50000);

        $this->getJson('/envio/cotizar?cp=1425')->assertOk()->assertJson(['ok' => true, 'zone' => 'CABA', 'cost' => 7500]);
        $this->getJson('/envio/cotizar?cp=B1636')->assertJson(['zone' => 'GBA']);
        $this->getJson('/envio/cotizar?cp=5000')->assertJson(['zone' => 'Resto del país']);
        $this->getJson('/en/envio/cotizar?cp=1425')->assertJson(['zone' => 'Buenos Aires City']);
        $this->getJson('/envio/cotizar?cp=abc')->assertStatus(422);
    }

    public function test_shipping_quote_by_address_when_postal_code_is_missing(): void
    {
        $this->cartWith(1, 50000);
        Http::fake([
            'nominatim.openstreetmap.org/*' => Http::sequence()
                ->push([['address' => ['postcode' => 'B1824']]])
                ->push([]),
        ]);

        // Geocodifica la dirección y usa el CP que devuelve.
        $this->getJson('/envio/cotizar?address=Hipolito+Yrigoyen+4000&city=Lanus&province=Buenos+Aires')
            ->assertOk()->assertJson(['postal_code' => '1824', 'zone' => 'GBA']);
        // Sin resultado: CP de la capital de la provincia; en Buenos Aires no hay respaldo.
        $this->getJson('/envio/cotizar?address=Calle+falsa&city=Mendoza&province=Mendoza')
            ->assertOk()->assertJson(['postal_code' => '5500', 'zone' => 'Resto del país']);
        $this->getJson('/envio/cotizar?city=Ningunlado&province=Buenos+Aires')->assertStatus(422);
    }

    public function test_free_shipping_over_threshold(): void
    {
        $this->cartWith(1, 450000);

        $this->getJson('/envio/cotizar?cp=1425')->assertJson(['cost' => 0]);
    }

    public function test_checkout_creates_order_with_server_side_totals_and_redirects_to_mercado_pago(): void
    {
        $this->fakePreference();
        $this->cartWith(2, 100000);

        // Aunque el navegador mande montos, se ignoran.
        $response = $this->post('/checkout', $this->checkoutData(['total' => 1, 'price' => 1, 'shipping_cost' => 0]));

        $response->assertRedirect('https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=pref-1');
        $order = Order::first();
        $this->assertSame('pending', $order->status);
        $this->assertEquals(200000, $order->subtotal);
        $this->assertEquals(7500, $order->shipping_cost);
        $this->assertEquals(207500, $order->total);
        $this->assertSame(40, strlen($order->token));
        $this->get('/carrito')->assertSee('El carrito está vacío');

        Http::assertSent(function (Request $request) use ($order) {
            $items = $request['items'];

            return $request->hasHeader('Authorization', 'Bearer TEST-token')
                && $request['external_reference'] === $order->token
                && $items[0]['unit_price'] == 100000 && $items[0]['quantity'] === 2
                && $items[1]['id'] === 'ENVIO' && $items[1]['unit_price'] == 7500
                && str_contains($request['notification_url'], '/webhooks/mercadopago');
        });
    }

    public function test_pickup_has_no_shipping_cost(): void
    {
        $this->fakePreference();
        $this->cartWith(1, 100000);
        $pickup = ShippingZone::where('is_pickup', true)->first();

        $this->post('/checkout', $this->checkoutData(['delivery' => 'pickup', 'pickup_zone' => $pickup->id, 'postal_code' => null, 'address' => null]));

        $this->assertEquals(0, Order::first()->shipping_cost);
        $this->assertNull(Order::first()->address);
    }

    public function test_checkout_validation(): void
    {
        $this->cartWith();

        $this->post('/checkout', ['delivery' => 'shipping'])
            ->assertSessionHasErrors(['name', 'email', 'phone', 'address', 'city', 'province', 'terms']);
        $this->assertSame(0, Order::count());
    }

    public function test_webhook_with_valid_signature_marks_order_paid_and_sends_mails(): void
    {
        $order = $this->makeOrder();
        Http::fake(['api.mercadopago.com/v1/payments/111' => Http::response($this->paymentPayload($order))]);

        $this->signedWebhook('111')->assertOk();

        $order->refresh();
        $this->assertSame('paid', $order->status);
        $this->assertNotNull($order->paid_at);
        $this->assertSame('4242', $order->payments->first()->card_last_four);
        Mail::assertSent(OrderPaid::class, fn ($m) => $m->hasTo('ana@example.com'));
        Mail::assertSent(OrderPaidAdmin::class);

        // Idempotente: el mismo aviso otra vez no duplica pagos ni mails.
        $this->signedWebhook('111')->assertOk();
        $this->assertSame(1, $order->payments()->count());
        Mail::assertSent(OrderPaid::class, 1);
    }

    public function test_webhook_with_bad_signature_is_rejected(): void
    {
        $order = $this->makeOrder();
        Http::fake();

        $this->postJson('/webhooks/mercadopago?data.id=111&type=payment', ['type' => 'payment', 'data' => ['id' => '111']], [
            'x-signature' => 'ts='.time().',v1=falso', 'x-request-id' => 'req-1',
        ])->assertStatus(401);

        Http::assertNothingSent();
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_two_cards_pay_the_order_together(): void
    {
        $order = $this->makeOrder(); // total 207.500
        Http::fake([
            'api.mercadopago.com/v1/payments/201' => Http::response($this->paymentPayload($order, ['id' => 201, 'transaction_amount' => 100000])),
            'api.mercadopago.com/v1/payments/202' => Http::response($this->paymentPayload($order, ['id' => 202, 'transaction_amount' => 107500, 'payment_type_id' => 'debit_card', 'payment_method_id' => 'debmaster'])),
        ]);

        $this->signedWebhook('201');
        $this->assertSame('in_process', $order->fresh()->status); // falta el resto

        $this->signedWebhook('202');
        $this->assertSame('paid', $order->fresh()->status);
        $this->assertEquals(207500, $order->fresh()->amountApproved());
    }

    public function test_payment_from_another_order_or_currency_is_ignored(): void
    {
        $order = $this->makeOrder();
        $other = $this->makeOrder();
        Http::fake([
            'api.mercadopago.com/v1/payments/300' => Http::response($this->paymentPayload($other, ['id' => 300])),
            'api.mercadopago.com/v1/payments/301' => Http::response($this->paymentPayload($order, ['id' => 301, 'currency_id' => 'USD'])),
        ]);

        // Volver de Mercado Pago con el payment_id de OTRO pedido no marca este como pagado.
        $this->get("/pedido/{$order->token}?payment_id=300&status=approved")->assertOk();
        $this->assertSame('pending', $order->fresh()->status);

        $this->signedWebhook('301');
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_rejected_payment_allows_retry(): void
    {
        $order = $this->makeOrder();
        Http::fake([
            'api.mercadopago.com/v1/payments/400' => Http::response($this->paymentPayload($order, ['id' => 400, 'status' => 'rejected', 'status_detail' => 'cc_rejected_insufficient_amount'])),
            'api.mercadopago.com/checkout/preferences' => Http::response(['id' => 'pref-2', 'init_point' => 'https://mp.test/retry']),
        ]);

        $this->get("/pedido/{$order->token}?payment_id=400")->assertOk()->assertSee('Reintentar el pago');
        $this->assertSame('rejected', $order->fresh()->status);

        $this->post("/pedido/{$order->token}/pagar")->assertRedirect('https://mp.test/retry');
    }

    public function test_paid_order_cannot_be_paid_again_and_tokens_are_not_guessable(): void
    {
        $order = $this->makeOrder(['status' => 'paid']);

        $this->post("/pedido/{$order->token}/pagar")->assertForbidden();
        $this->get('/pedido/'.$order->id)->assertNotFound();
    }

    public function test_customer_can_cancel_an_unpaid_order(): void
    {
        $order = $this->makeOrder();

        $this->get("/pedido/{$order->token}")->assertSee('Cancelar pedido');
        $this->post("/pedido/{$order->token}/cancelar")->assertRedirect("/pedido/{$order->token}");

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->get("/pedido/{$order->token}")->assertSee('Pedido cancelado')->assertDontSee('Cancelar pedido');
        $this->post("/pedido/{$order->token}/pagar")->assertForbidden();
    }

    public function test_customer_cannot_cancel_a_paid_order(): void
    {
        $order = $this->makeOrder(['status' => 'paid', 'paid_at' => now()]);

        $this->post("/pedido/{$order->token}/cancelar")->assertForbidden();
        $this->assertSame('paid', $order->fresh()->status);
    }

    public function test_payment_finished_after_cancelling_marks_the_order_paid(): void
    {
        $order = $this->makeOrder();
        $this->post("/pedido/{$order->token}/cancelar");
        Http::fake(['api.mercadopago.com/v1/payments/500' => Http::response($this->paymentPayload($order, ['id' => 500]))]);

        $this->signedWebhook('500')->assertOk();

        $this->assertSame('paid', $order->fresh()->status);
        Mail::assertSent(OrderPaidAdmin::class);
    }

    public function test_admin_cancelled_paid_order_stays_cancelled_on_resync(): void
    {
        $order = $this->makeOrder();
        Http::fake(['api.mercadopago.com/v1/payments/600' => Http::response($this->paymentPayload($order, ['id' => 600]))]);
        $this->signedWebhook('600');
        $order->refresh()->update(['status' => 'cancelled']);

        $this->signedWebhook('600')->assertOk();

        $this->assertSame('cancelled', $order->fresh()->status);
    }

    public function test_admin_can_ship_an_order(): void
    {
        $order = $this->makeOrder(['status' => 'paid']);

        $this->actingAs(User::factory()->create())
            ->put("/admin/pedidos/{$order->id}", ['action' => 'ship', 'tracking_code' => 'AB123'])->assertRedirect();

        $this->assertSame('shipped', $order->fresh()->status);
        $this->assertSame('AB123', $order->fresh()->tracking_code);
        $this->get("/pedido/{$order->token}")->assertSee('AB123');
    }

    public function test_delivered_order_asks_for_a_google_review(): void
    {
        config(['tnl.google_review' => 'https://g.page/r/test/review']);
        $order = $this->makeOrder(['status' => 'paid']);

        $this->get("/pedido/{$order->token}")->assertDontSee('Dejar una reseña');

        $order->update(['status' => 'delivered', 'delivered_at' => now()]);
        $this->get("/pedido/{$order->token}")->assertSee('Dejar una reseña')->assertSee('href="https://g.page/r/test/review"', false);
    }

    public function test_admin_can_charge_with_a_manual_payment_link(): void
    {
        $order = $this->makeOrder();
        $admin = $this->actingAs(User::factory()->create());

        $admin->put("/admin/pedidos/{$order->id}", ['action' => 'payment_link', 'payment_link' => 'javascript:alert(1)'])
            ->assertSessionHasErrors('payment_link');
        $admin->put("/admin/pedidos/{$order->id}", ['action' => 'payment_link', 'payment_link' => 'https://mpago.la/abc123'])
            ->assertRedirect();

        // El admin ve los botones con el mensaje armado y el cliente paga con ese link (aunque la API esté configurada).
        $this->get("/admin/pedidos/{$order->id}")->assertSee('Enviar por WhatsApp')->assertSee(rawurlencode('https://mpago.la/abc123'), false);
        $this->get("/pedido/{$order->token}")->assertSee('href="https://mpago.la/abc123"', false);

        // Para marcarlo pagado hace falta el número de operación de Mercado Pago (sirve para reintegrar).
        $admin->put("/admin/pedidos/{$order->id}", ['action' => 'paid'])->assertSessionHasErrors('operation_id');
        $admin->put("/admin/pedidos/{$order->id}", ['action' => 'paid', 'operation_id' => '12ab'])->assertSessionHasErrors('operation_id');
        $admin->put("/admin/pedidos/{$order->id}", ['action' => 'paid', 'operation_id' => '129193555737'])->assertRedirect();
        $this->assertSame('paid', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->paid_at);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'provider' => 'manual', 'provider_id' => '129193555737', 'status' => 'approved', 'amount' => 207500]);
        $this->get("/pedido/{$order->token}")->assertDontSee('https://mpago.la/abc123')->assertSee('Link de pago de Mercado Pago');
        $this->get("/admin/pedidos/{$order->id}")->assertSee('activities?q=129193555737', false)->assertSee('Marcar como reintegrado');

        // La misma operación no se puede asociar a otro pedido.
        $other = $this->makeOrder();
        $admin->put("/admin/pedidos/{$other->id}", ['action' => 'paid', 'operation_id' => '129193555737'])->assertSessionHasErrors('operation_id');

        $admin->put("/admin/pedidos/{$order->id}", ['action' => 'refunded'])->assertRedirect();
        $this->assertSame('refunded', $order->fresh()->status);
        $this->assertDatabaseHas('payments', ['provider_id' => '129193555737', 'status' => 'refunded', 'amount_refunded' => 207500]);
    }

    public function test_general_payment_link_is_used_when_mercado_pago_api_is_off(): void
    {
        config(['services.mercadopago.access_token' => null, 'tnl.payment_link' => 'https://link.mercadopago.com.ar/tnl']);
        $order = $this->makeOrder();

        $this->get("/pedido/{$order->token}")
            ->assertSee('href="https://link.mercadopago.com.ar/tnl"', false)
            ->assertSee($order->number());
        $this->assertStringContainsString('ingresá el monto', $order->paymentLinkMessage());

        // Con la API activa y sin link propio, sigue el pago automático.
        config(['services.mercadopago.access_token' => 'TEST-token']);
        $this->get("/pedido/{$order->token}")->assertDontSee('https://link.mercadopago.com.ar/tnl');
    }

    public function test_google_analytics_ecommerce_events(): void
    {
        $product = Product::create(['code' => 'P1', 'name' => 'Placa de video', 'price' => 100000, 'stock' => 'DISPONIBLE']);

        $this->get("/tienda/producto/{$product->code}")->assertOk()
            ->assertSee('G-MB8W84WP8R', false)->assertSee("gtag('event', 'view_item'", false);

        $this->from("/tienda/producto/{$product->code}")->post("/carrito/{$product->code}", ['qty' => 2]);
        $this->get("/tienda/producto/{$product->code}")->assertSee("gtag('event', 'add_to_cart'", false)->assertSee('"quantity":2', false);
        $this->get('/carrito')->assertSee("gtag('event', 'view_cart'", false);
        $this->get('/checkout')->assertSee("gtag('event', 'begin_checkout'", false);

        $pending = $this->makeOrder();
        $this->get("/pedido/{$pending->token}")->assertOk()->assertDontSee("'purchase'", false);

        $paid = $this->makeOrder(['status' => 'paid', 'paid_at' => now()]);
        $this->get("/pedido/{$paid->token}")->assertOk()
            ->assertSee("gtag('event', 'purchase'", false)->assertSee('"transaction_id":"'.$paid->number().'"', false)->assertSee('"value":207500', false);
    }

    private function makeOrder(array $attrs = []): Order
    {
        $order = Order::create($attrs + [
            'customer_name' => 'Ana Pérez', 'email' => 'ana@example.com', 'phone' => '1144445555',
            'shipping_zone_name' => 'CABA', 'address' => 'Calle 1', 'city' => 'CABA', 'province' => 'CABA', 'postal_code' => '1425',
            'subtotal' => 200000, 'shipping_cost' => 7500, 'total' => 207500,
        ]);
        $order->items()->create(['code' => 'P1', 'name' => 'Placa de video', 'unit_price' => 100000, 'qty' => 2, 'subtotal' => 200000]);

        return $order;
    }

    private function signedWebhook(string $id)
    {
        $ts = (string) time();
        $signature = hash_hmac('sha256', "id:{$id};request-id:req-{$id};ts:{$ts};", 'secreto');

        return $this->postJson("/webhooks/mercadopago?data.id={$id}&type=payment", ['type' => 'payment', 'data' => ['id' => $id]], [
            'x-signature' => "ts={$ts},v1={$signature}", 'x-request-id' => "req-{$id}",
        ]);
    }
}
