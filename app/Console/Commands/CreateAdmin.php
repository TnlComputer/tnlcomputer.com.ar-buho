<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateAdmin extends Command
{
    protected $signature = 'tnl:admin {email} {--name=Jorge}';

    protected $description = 'Crea o actualiza un usuario del panel /admin (pide la contraseña)';

    public function handle(): int
    {
        $password = $this->secret('Contraseña (mínimo 10 caracteres)');
        if (strlen((string) $password) < 10) {
            $this->error('La contraseña es muy corta.');

            return self::FAILURE;
        }

        $user = User::updateOrCreate(
            ['email' => $this->argument('email')],
            ['name' => $this->option('name'), 'password' => $password],
        );

        $this->info("Usuario {$user->email} listo. Ingresá en ".route('admin.login'));

        return self::SUCCESS;
    }
}
