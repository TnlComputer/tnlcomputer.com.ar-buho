<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\Totp;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function hasTwoFactor(): bool
    {
        return $this->two_factor_confirmed_at !== null && filled($this->two_factor_secret);
    }

    public function verifyTwoFactorCode(string $code): bool
    {
        return $this->hasTwoFactor() && Totp::verify($this->two_factor_secret, $code, "user{$this->id}");
    }

    /**
     * Genera códigos de recuperación nuevos (invalida los anteriores) y los devuelve en claro para mostrarlos
     * una sola vez. En la base queda solo el hash.
     *
     * @return list<string>
     */
    public function regenerateRecoveryCodes(): array
    {
        $codes = array_map(fn () => Str::lower(Str::random(5).'-'.Str::random(5)), range(1, 8));
        $this->forceFill(['two_factor_recovery_codes' => array_map(fn ($c) => hash('sha256', $c), $codes)])->save();

        return $codes;
    }

    /** Ingreso con un código de recuperación: sirve una sola vez. */
    public function useRecoveryCode(string $code): bool
    {
        $hash = hash('sha256', Str::lower(trim($code)));
        $codes = $this->two_factor_recovery_codes ?? [];

        foreach ($codes as $i => $stored) {
            if (hash_equals($stored, $hash)) {
                unset($codes[$i]);
                $this->forceFill(['two_factor_recovery_codes' => array_values($codes)])->save();

                return true;
            }
        }

        return false;
    }

    public function disableTwoFactor(): void
    {
        $this->forceFill(['two_factor_secret' => null, 'two_factor_recovery_codes' => null, 'two_factor_confirmed_at' => null])->save();
    }
}
