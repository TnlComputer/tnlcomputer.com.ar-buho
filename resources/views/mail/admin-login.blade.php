<x-mail::message>
# Ingreso al panel

Se ingresó al panel de administración con la cuenta **{{ $user->email }}**.

**Fecha:** {{ now()->format('d/m/Y H:i') }}
**IP:** {{ $ip }}
**Navegador:** {{ \Illuminate\Support\Str::limit($userAgent, 150) }}
@if ($usedRecoveryCode)
**Se usó un código de recuperación.** Te quedan {{ count($user->two_factor_recovery_codes ?? []) }}.
@endif
@unless ($user->hasTwoFactor())

La cuenta todavía no tiene la verificación en dos pasos: activala en Panel → Seguridad.
@endunless

<x-mail::panel>
Si no fuiste vos, entrá al panel, cambiá la contraseña (Panel → Seguridad) y revisá los últimos cambios.
</x-mail::panel>
</x-mail::message>
