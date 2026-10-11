<?php

namespace Tests\Feature;

use App\Mail\NewMessage;
use App\Models\Category;
use App\Models\Message;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\HostingPlanSeeder;
use Database\Seeders\PortfolioSeeder;
use Database\Seeders\ShippingZoneSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([HostingPlanSeeder::class, PortfolioSeeder::class, ShippingZoneSeeder::class]);
    }

    private function product(array $attrs = []): Product
    {
        $category = Category::firstOrCreate(['slug' => 'memorias-ram'], ['name' => 'Memorias RAM', 'name_en' => 'RAM memory']);

        return Product::create($attrs + [
            'code' => 'P1', 'name' => 'Memoria 8GB', 'price' => 25000, 'stock' => 'DISPONIBLE', 'category_id' => $category->id,
        ]);
    }

    public function test_public_pages_render_in_both_languages(): void
    {
        $this->product(['featured' => true]);

        foreach (['/', '/hosting', '/diseno-web', '/service', '/contacto', '/portfolio', '/tienda', '/carrito', '/tienda/producto/P1', '/tienda/rubro/memorias-ram'] as $url) {
            $this->get($url)->assertOk()->assertSee('<html lang="es">', false);
            $this->get('/en'.rtrim($url, '/'))->assertOk()->assertSee('<html lang="en">', false);
        }
    }

    public function test_english_pages_are_translated_and_link_to_english(): void
    {
        $this->product();

        $this->get('/en')->assertSee('Technology that')->assertSee('Visit the shop')->assertDontSee('Ver la tienda')
            ->assertSee(url('/en/tienda'), false)->assertSee('View site in Spanish');
        $this->get('/')->assertSee('Ver sitio en inglés');
        $this->get('/en/tienda/rubro/memorias-ram')->assertSee('RAM memory');
        $this->get('/en/portfolio')->assertSee('About me')->assertSee('Management system');
        $this->get('/en/no-existe')->assertNotFound()->assertSee('Page not found');
    }

    public function test_language_switch_keeps_the_same_page(): void
    {
        $this->product();

        $this->get('/tienda/producto/P1')->assertSee('href="'.url('/en/tienda/producto/P1').'"', false);
        $this->get('/en/hosting')->assertSee('href="'.url('/hosting').'"', false);
    }

    public function test_old_urls_redirect_permanently(): void
    {
        $this->get('/hosting.html')->assertStatus(301)->assertRedirect('/hosting');
        $this->get('/hardware.html')->assertStatus(301)->assertRedirect('/tienda');
    }

    public function test_out_of_stock_and_inactive_products_are_hidden(): void
    {
        $this->product(['code' => 'A', 'name' => 'Visible']);
        $this->product(['code' => 'B', 'name' => 'Agotado', 'stock' => Product::SIN_STOCK]);
        $this->product(['code' => 'C', 'name' => 'Inactivo', 'active' => false]);

        $this->get('/tienda')->assertSee('Visible')->assertDontSee('Agotado')->assertDontSee('Inactivo');
        $this->get('/tienda/producto/C')->assertNotFound();
    }

    public function test_search_does_not_break_on_sql_characters(): void
    {
        $this->product();

        $this->get("/tienda?q=' OR 1=1 --")->assertOk()->assertDontSee('Memoria 8GB');
    }

    public function test_hosting_prices_are_shown_plus_vat(): void
    {
        $this->get('/hosting')->assertSee('+ IVA');
        $this->get('/en/hosting')->assertSee('+ VAT');
    }

    public function test_contact_form_stores_message_and_sends_mail(): void
    {
        Mail::fake();

        $this->post('/contacto', ['name' => 'Ana', 'email' => 'ana@example.com', 'subject' => 'Consulta', 'body' => 'Hola'])->assertRedirect();
        $this->post('/en/contacto', ['name' => 'John', 'email' => 'john@example.com', 'subject' => 'Hi', 'body' => 'Hello'])->assertRedirect();

        $this->assertDatabaseHas('messages', ['type' => 'contacto', 'name' => 'Ana', 'locale' => 'es']);
        $this->assertDatabaseHas('messages', ['name' => 'John', 'locale' => 'en']);
        Mail::assertSent(NewMessage::class, 2);
    }

    public function test_contact_form_validates_and_honeypot_discards_bots(): void
    {
        $this->post('/contacto', [])->assertSessionHasErrors(['name', 'email', 'subject', 'body']);

        $this->post('/contacto', ['name' => 'Bot', 'email' => 'bot@example.com', 'subject' => 'x', 'body' => 'x', 'website' => 'spam'])->assertRedirect();
        $this->assertSame(0, Message::count());
    }

    public function test_service_form_only_accepts_known_reasons(): void
    {
        $data = ['name' => 'Ana', 'phone' => '1144445555', 'body' => 'No prende'];

        $this->post('/service', $data + ['subject' => 'Inventado'])->assertSessionHasErrors('subject');
        $this->post('/service', $data + ['subject' => 'No enciende'])->assertSessionHasNoErrors();
    }

    public function test_security_headers_are_present(): void
    {
        $this->get('/')->assertHeader('X-Frame-Options', 'SAMEORIGIN')->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_admin_requires_login(): void
    {
        foreach (['/admin', '/admin/productos', '/admin/pedidos', '/admin/zonas-envio'] as $url) {
            $this->get($url)->assertRedirect('/admin/login');
        }
    }

    public function test_admin_pages_render_for_logged_user(): void
    {
        $this->product();
        $user = User::factory()->create();

        foreach (['/admin', '/admin/productos', '/admin/productos/create', '/admin/productos/P1/edit', '/admin/productos/importar',
            '/admin/rubros', '/admin/hosting', '/admin/proyectos', '/admin/skills', '/admin/certificaciones', '/admin/mensajes',
            '/admin/pedidos', '/admin/zonas-envio', '/admin/zonas-envio/create'] as $url) {
            $this->actingAs($user)->get($url)->assertOk();
        }
    }

    public function test_admin_can_toggle_offer(): void
    {
        $product = $this->product();

        $this->actingAs(User::factory()->create())->put("/admin/productos/{$product->code}", ['toggle' => 'featured'])->assertRedirect();

        $this->assertTrue($product->fresh()->featured);
    }
}
