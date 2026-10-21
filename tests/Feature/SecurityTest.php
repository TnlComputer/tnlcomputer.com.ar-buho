<?php

namespace Tests\Feature;

use App\Mail\AdminLogin;
use App\Models\Order;
use App\Models\User;
use App\Services\MercadoPago;
use App\Support\Totp;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    private function admin(bool $twoFactor = false): User
    {
        $user = User::create(['name' => 'Jorge', 'email' => 'admin@example.com', 'password' => 'una-clave-larga']);
        if ($twoFactor) {
            $user->forceFill(['two_factor_secret' => Totp::generateSecret(), 'two_factor_confirmed_at' => now()])->save();
        }

        return $user;
    }

    /** Código válido ahora para la clave (lo mismo que mostraría la app del celular). */
    private function currentCode(string $secret): string
    {
        $at = new \ReflectionMethod(Totp::class, 'at');

        return $at->invoke(null, $secret, intdiv(time(), 30));
    }

    public function test_totp_matches_rfc_6238_vectors(): void
    {
        $at = new \ReflectionMethod(Totp::class, 'at');
        $secret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ'; // "12345678901234567890" en base32

        $this->assertSame('287082', $at->invoke(null, $secret, intdiv(59, 30)));
        $this->assertSame('081804', $at->invoke(null, $secret, intdiv(1111111109, 30)));
        $this->assertSame(32, strlen(Totp::generateSecret()));
    }

    public function test_totp_code_cannot_be_reused(): void
    {
        $secret = Totp::generateSecret();
        $code = $this->currentCode($secret);

        $this->assertTrue(Totp::verify($secret, $code, 'x'));
        $this->assertFalse(Totp::verify($secret, $code, 'x'));
        $this->assertFalse(Totp::verify($secret, '12345', 'y'));
    }

    public function test_login_without_two_factor_sends_alert(): void
    {
        $this->admin();

        $this->post('/admin/login', ['email' => 'admin@example.com', 'password' => 'una-clave-larga'])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
        Mail::assertSent(AdminLogin::class);
    }

    public function test_login_with_two_factor_requires_the_code(): void
    {
        $user = $this->admin(twoFactor: true);

        $this->post('/admin/login', ['email' => 'admin@example.com', 'password' => 'una-clave-larga'])->assertRedirect(route('admin.two-factor'));
        $this->assertGuest();
        $this->get('/admin')->assertRedirect(route('admin.login'));

        $this->post('/admin/verificacion', ['code' => '000000'])->assertSessionHasErrors('code');
        $this->assertGuest();

        $this->post('/admin/verificacion', ['code' => $this->currentCode($user->two_factor_secret)])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_two_factor_challenge_needs_the_password_first(): void
    {
        $this->admin(twoFactor: true);

        $this->get('/admin/verificacion')->assertRedirect(route('admin.login'));
        $this->post('/admin/verificacion', ['code' => '123456'])->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_recovery_code_works_only_once(): void
    {
        $user = $this->admin(twoFactor: true);
        $codes = $user->regenerateRecoveryCodes();

        $this->post('/admin/login', ['email' => 'admin@example.com', 'password' => 'una-clave-larga']);
        $this->post('/admin/verificacion', ['recovery_code' => strtoupper($codes[0])])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
        $this->assertCount(7, $user->fresh()->two_factor_recovery_codes);

        $this->post('/admin/logout');
        $this->post('/admin/login', ['email' => 'admin@example.com', 'password' => 'una-clave-larga']);
        $this->post('/admin/verificacion', ['recovery_code' => $codes[0]])->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_admin_enables_two_factor_from_the_panel(): void
    {
        $user = $this->admin();

        $this->actingAs($user)->get('/admin/seguridad')->assertOk()->assertSee('<svg', false);
        $secret = session('two_factor.setup');

        $this->post('/admin/seguridad/2fa', ['code' => '000000'])->assertSessionHasErrors('code');
        $this->post('/admin/seguridad/2fa', ['code' => $this->currentCode($secret)])->assertRedirect(route('admin.security'));

        $user->refresh();
        $this->assertTrue($user->hasTwoFactor());
        $this->assertSame($secret, $user->two_factor_secret);
        $this->assertCount(8, $user->two_factor_recovery_codes);
        // La clave queda cifrada en la base.
        $this->assertStringNotContainsString($secret, (string) \DB::table('users')->value('two_factor_secret'));

        $this->delete('/admin/seguridad/2fa', ['disable_password' => 'mala'])->assertSessionHasErrors('disable_password');
        $this->delete('/admin/seguridad/2fa', ['disable_password' => 'una-clave-larga']);
        $this->assertFalse($user->fresh()->hasTwoFactor());
    }

    public function test_login_is_throttled_per_account(): void
    {
        $this->admin();

        // Desde IPs distintas, para que solo cuente el límite por cuenta.
        foreach (range(1, 10) as $i) {
            $this->withServerVariables(['REMOTE_ADDR' => "10.0.0.{$i}"])
                ->post('/admin/login', ['email' => 'admin@example.com', 'password' => 'mala'])->assertSessionHasErrors('email');
        }
        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.99'])
            ->post('/admin/login', ['email' => 'admin@example.com', 'password' => 'una-clave-larga'])->assertStatus(429);
        $this->assertGuest();
    }

    public function test_changing_password_requires_the_current_one(): void
    {
        $user = $this->admin();
        $this->actingAs($user);

        $this->put('/admin/seguridad/contrasena', ['current_password' => 'mala', 'password' => 'otra-clave-bien-larga', 'password_confirmation' => 'otra-clave-bien-larga'])
            ->assertSessionHasErrors('current_password');
    }

    public function test_webhook_signature_is_mandatory_in_production(): void
    {
        config(['services.mercadopago.webhook_secret' => null]);
        $mp = new MercadoPago;

        $this->assertTrue($mp->validSignature(Request::create('/'), '123'));
        $this->app->detectEnvironment(fn () => 'production');
        $this->assertFalse($mp->validSignature(Request::create('/'), '123'));
    }

    public function test_csp_report_only_with_nonce_on_inline_scripts(): void
    {
        $response = $this->get('/');
        $policy = $response->headers->get('Content-Security-Policy-Report-Only');

        $this->assertNotNull($policy);
        $this->assertStringContainsString("object-src 'none'", $policy);
        preg_match("/'nonce-([^']+)'/", $policy, $m);
        $response->assertSee('<script nonce="'.$m[1].'">', false);

        config(['tnl.csp' => 'enforce']);
        $this->assertNotNull($this->get('/')->headers->get('Content-Security-Policy'));
    }

    public function test_csp_reports_are_logged(): void
    {
        $this->call('POST', '/csp-report', [], [], [], ['CONTENT_TYPE' => 'application/csp-report'],
            json_encode(['csp-report' => ['document-uri' => 'https://x/', 'blocked-uri' => 'https://evil.example/x.js']]))
            ->assertNoContent();
    }

    public function test_order_status_changes_are_recorded(): void
    {
        $order = Order::create(['customer_name' => 'Ana', 'email' => 'ana@example.com', 'phone' => '1144445555', 'subtotal' => 100, 'total' => 100]);
        $admin = $this->admin();

        $this->actingAs($admin)->put("/admin/pedidos/{$order->id}", ['action' => 'cancel']);

        $events = $order->events()->get();
        $this->assertCount(2, $events);
        $this->assertSame([null, 'pending'], [$events[0]->from_status, $events[0]->to_status]);
        $this->assertSame(['pending', 'cancelled', 'admin', $admin->id], [$events[1]->from_status, $events[1]->to_status, $events[1]->source, $events[1]->user_id]);
        $this->get("/admin/pedidos/{$order->id}")->assertSee('Jorge');
    }

    public function test_orders_with_payments_cannot_be_deleted(): void
    {
        $order = Order::create(['customer_name' => 'Ana', 'email' => 'ana@example.com', 'phone' => '1144445555', 'subtotal' => 100, 'total' => 100]);
        $order->payments()->create(['provider' => 'manual', 'provider_id' => '123456', 'status' => 'approved', 'amount' => 100]);

        $this->expectException(QueryException::class);
        $order->delete();
    }

    protected function tearDown(): void
    {
        Cache::flush();
        parent::tearDown();
    }
}
