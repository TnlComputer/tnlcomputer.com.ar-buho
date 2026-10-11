<?php

namespace Tests\Feature;

use App\Http\Controllers\InstallController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstallTest extends TestCase
{
    use RefreshDatabase;

    private string $envBackup;

    protected function setUp(): void
    {
        putenv('INSTALL_TOKEN=clave-de-prueba-123');
        parent::setUp();
        @unlink(InstallController::lockFile());
        // El instalador escribe APP_KEY en el .env: se trabaja sobre una copia.
        $this->envBackup = is_file(base_path('.env')) ? file_get_contents(base_path('.env')) : '';
    }

    protected function tearDown(): void
    {
        @unlink(InstallController::lockFile());
        file_put_contents(base_path('.env'), $this->envBackup);
        putenv('INSTALL_TOKEN');
        parent::tearDown();
    }

    public function test_installer_is_hidden_without_token(): void
    {
        putenv('INSTALL_TOKEN');
        $this->refreshApplication();

        $this->get('/instalar')->assertNotFound();
    }

    public function test_wrong_token_is_rejected(): void
    {
        $this->get('/instalar')->assertOk()->assertSee('Requisitos');
        $this->post('/instalar', $this->form(['token' => 'otra']))->assertForbidden();
        $this->assertFileDoesNotExist(InstallController::lockFile());
    }

    public function test_installs_creates_admin_and_disables_itself(): void
    {
        $this->post('/instalar', $this->form())->assertOk()->assertSee('Listo');

        $this->assertTrue(User::where('email', 'jorge@example.com')->exists());
        $this->assertFileExists(InstallController::lockFile());
        $this->get('/instalar')->assertNotFound();
    }

    private function form(array $overrides = []): array
    {
        return $overrides + [
            'token' => 'clave-de-prueba-123', 'name' => 'Jorge', 'email' => 'jorge@example.com',
            'password' => 'una-clave-segura', 'password_confirmation' => 'una-clave-segura',
        ];
    }
}
