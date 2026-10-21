<?php

namespace Tests\Feature;

use App\Mail\NewMessage;
use App\Models\Category;
use App\Models\Message;
use App\Models\Product;
use App\Models\User;
use App\Support\FormGuard;
use Database\Seeders\HostingPlanSeeder;
use Database\Seeders\PortfolioSeeder;
use Database\Seeders\ShippingZoneSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
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

    public function test_portfolio_downloads_as_pdf_in_both_languages(): void
    {
        $this->get('/portfolio')->assertSee(url('/portfolio/descargar'), false);
        $this->get('/en/portfolio')->assertSee(url('/en/portfolio/descargar'), false)->assertSee('Download PDF');

        $es = $this->get('/portfolio/descargar')->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $es->getContent());
        $this->assertStringContainsString('jorge-gustavo-martinez-portfolio.pdf', $es->headers->get('Content-Disposition'));

        $en = $this->get('/en/portfolio/descargar')->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('portfolio-en.pdf', $en->headers->get('Content-Disposition'));
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
        $this->get('/hardware')->assertStatus(301)->assertRedirect('/tienda');
    }

    public function test_sitemap_lists_pages_and_visible_products(): void
    {
        $this->product(['code' => 'A']);
        $this->product(['code' => 'B', 'active' => false]);

        $this->get('/sitemap.xml')->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=utf-8')
            ->assertSee('<?xml version="1.0" encoding="UTF-8"?>'."\n<urlset", false)
            ->assertSee('<loc>'.url('/hosting').'</loc>', false)
            ->assertSee('<loc>'.url('/en/tienda/producto/A').'</loc>', false)
            ->assertDontSee('/tienda/producto/B', false);
    }

    public function test_policies_page_lists_shipping_zones_and_withdrawal(): void
    {
        $this->seed(\Database\Seeders\ShippingZoneSeeder::class);

        $this->get('/envios-y-devoluciones')->assertOk()
            ->assertSee('Retiro sin cargo')->assertSee('Resto del país')
            ->assertSee('id="arrepentimiento"', false)->assertSee('10 días corridos');
        $this->get('/en/envios-y-devoluciones')->assertOk()->assertSee('Right of withdrawal')->assertSee('Free pickup');
        $this->get('/')->assertSee(url('/envios-y-devoluciones').'#arrepentimiento', false);
        $this->get('/sitemap.xml')->assertSee('<loc>'.url('/envios-y-devoluciones').'</loc>', false);
    }

    public function test_google_feed_lists_buyable_products_with_photo(): void
    {
        $img = 'img/productos/catalogo/41817.png';
        $this->product(['code' => 'A', 'name' => 'Caddy SATA', 'price' => 12500, 'image' => $img]);
        $this->product(['code' => 'B', 'name' => 'Sin foto']);
        $this->product(['code' => 'C', 'name' => 'Cooler (solo para PC armada)', 'image' => $img]);
        $this->product(['code' => 'D', 'name' => 'Inactivo', 'image' => $img, 'active' => false]);

        $this->get('/feed/google.xml')->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=utf-8')
            ->assertSee('<?xml version="1.0" encoding="UTF-8"?>'."\n<rss", false)
            ->assertSee('<g:id>A</g:id>', false)
            ->assertSee('<g:price>12500.00 ARS</g:price>', false)
            ->assertSee('<g:link>'.url('/tienda/producto/A').'</g:link>', false)
            ->assertDontSee('<g:id>B</g:id>', false)
            ->assertDontSee('<g:id>C</g:id>', false)
            ->assertDontSee('<g:id>D</g:id>', false);
    }

    public function test_product_specs_are_edited_shown_and_sent_to_google(): void
    {
        $this->actingAs(User::create(['name' => 'A', 'email' => 'a@example.com', 'password' => 'x']));
        $product = $this->product(['code' => 'K1', 'name' => 'Teclado Redragon Kumara', 'image' => 'img/productos/catalogo/41817.png']);

        $this->put("/admin/productos/{$product->code}", [
            'code' => 'K1', 'name' => $product->name, 'price' => 1000, 'active' => '1',
            'specs' => "Conexión: USB\n\nlínea sin valor\nSwitches:  Outemu Red ",
        ])->assertRedirect();

        $this->assertSame([['Conexión', 'USB'], ['Switches', 'Outemu Red']], $product->fresh()->specs);
        $this->get('/admin/productos/K1/edit')->assertSee("Conexión: USB\nSwitches: Outemu Red");
        $this->get('/tienda/producto/K1')->assertSee('Características')->assertSee('Outemu Red');
        $this->get('/feed/google.xml')->assertSee('<g:attribute_name>Switches</g:attribute_name>', false)
            ->assertSee('<g:attribute_value>Outemu Red</g:attribute_value>', false);
    }

    public function test_extra_photos_are_uploaded_removed_and_shown_in_gallery(): void
    {
        $this->actingAs(User::create(['name' => 'A', 'email' => 'a@example.com', 'password' => 'x']));
        $product = $this->product(['code' => 'G1', 'image' => 'img/productos/catalogo/41817.png']);
        $form = ['code' => 'G1', 'name' => $product->name, 'price' => 1000, 'active' => '1'];

        $this->put('/admin/productos/G1', $form + ['photos' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.jpg')]])->assertRedirect();
        [$first, $second] = $product->fresh()->images;

        try {
            $this->get('/tienda/producto/G1')->assertSee('data-gallery-thumb="'.asset($second).'"', false);
            $this->get('/feed/google.xml')->assertSee('<g:additional_image_link>'.asset($first), false);

            $this->put('/admin/productos/G1', $form + ['remove_images' => [$first]])->assertRedirect();
            $this->assertSame([$second], $product->fresh()->images);
            $this->assertFileDoesNotExist(public_path($first));
        } finally {
            File::delete([public_path($first), public_path($second)]);
        }
    }

    public function test_google_feed_sends_brand_and_part_number_when_known(): void
    {
        $img = 'img/productos/catalogo/41817.png';
        $this->product(['code' => '19648', 'name' => 'Disco Solido SSD 480GB SATA KINGSTON A400 (SA400S37/480G)', 'image' => $img]);
        $this->product(['code' => '41947', 'name' => 'Webcam sin datos', 'image' => $img]);

        $xml = $this->get('/feed/google.xml')->assertOk()->getContent();
        $item = fn (string $id) => preg_match('#<g:id>'.$id.'</g:id>.*?</item>#s', $xml, $m) ? $m[0] : '';

        $this->assertStringContainsString('<g:brand>Kingston</g:brand>', $item('19648'));
        $this->assertStringContainsString('<g:mpn>SA400S37/480G</g:mpn>', $item('19648'));
        $this->assertStringNotContainsString('identifier_exists', $item('19648'));
        $this->assertStringContainsString('<g:identifier_exists>no</g:identifier_exists>', $item('41947'));
    }

    public function test_home_has_business_structured_data(): void
    {
        $this->get('/')->assertSee('{"@context":"https://schema.org","@type":"ComputerStore"', false);
        $this->get('/hosting')->assertDontSee('application/ld+json', false);
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

    public function test_forms_reject_submissions_faster_than_a_person(): void
    {
        Mail::fake();
        config(['tnl.form_min_seconds' => 3]);
        $data = ['name' => 'Ana', 'email' => 'ana@example.com', 'subject' => 'Consulta', 'body' => 'Hola'];

        // La página trae la hora de carga cifrada.
        $this->get('/contacto')->assertSee('name="'.FormGuard::FIELD.'"', false);

        // Sin el campo (bot que no cargó la página) o con un valor falsificado: se descarta en silencio.
        $this->post('/contacto', $data)->assertSessionHas('sent');
        $this->post('/contacto', $data + [FormGuard::FIELD => (string) now()->subHour()->timestamp])->assertSessionHas('sent');
        $this->assertSame(0, Message::count());

        // Enviado al instante: se le pide a la persona que lo intente de nuevo, sin perder lo que escribió.
        $this->post('/contacto', $data + [FormGuard::FIELD => FormGuard::stamp()])
            ->assertSessionHasErrors('form')
            ->assertSessionHasInput('body', 'Hola');
        $this->assertSame(0, Message::count());

        // Pasados los segundos mínimos, se guarda normalmente.
        $stamp = FormGuard::stamp();
        $this->travel(5)->seconds();
        $this->post('/contacto', $data + [FormGuard::FIELD => $stamp])->assertSessionHasNoErrors();
        $this->assertSame(1, Message::count());
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
