@extends('layouts.admin')

@section('title', 'Seguridad')

@section('content')
    <div class="grid max-w-5xl gap-6 xl:grid-cols-2">
        <div class="card p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-sans text-base font-semibold">Verificación en dos pasos</h2>
                @if ($user->hasTwoFactor())
                    <span class="chip !bg-emerald-50 !text-emerald-700 dark:!bg-emerald-950/50 dark:!text-emerald-300">Activada</span>
                @else
                    <span class="chip !bg-amber-50 !text-amber-700 dark:!bg-amber-950/50 dark:!text-amber-300">Desactivada</span>
                @endif
            </div>

            @if ($recoveryCodes)
                <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-900 dark:bg-amber-950/50 dark:text-amber-200">
                    <p class="font-semibold">Guardá estos códigos de recuperación en un lugar seguro (no en el celular).</p>
                    <p class="mt-1">Sirven para entrar si perdés el celular. Cada uno se usa una sola vez y no se vuelven a mostrar.</p>
                    <ul class="mt-3 grid grid-cols-2 gap-1 font-mono">
                        @foreach ($recoveryCodes as $code)<li>{{ $code }}</li>@endforeach
                    </ul>
                </div>
            @endif

            @if ($setup)
                <p class="mt-3 text-sm text-zinc-500">Además de la contraseña, el panel va a pedir un código del celular. Así, aunque alguien consiga la contraseña, no puede entrar.</p>
                <ol class="mt-5 list-decimal space-y-4 pl-5 text-sm">
                    <li>Instalá una app de autenticación: Google Authenticator, Microsoft Authenticator o Authy.</li>
                    <li>
                        Escaneá este código QR con la app:
                        <div class="mt-3 inline-block rounded-xl bg-white p-3">{!! $setup['qr'] !!}</div>
                        <p class="mt-2 text-xs text-zinc-500">O cargá la clave a mano: <span class="font-mono text-zinc-900 select-all dark:text-white">{{ $setup['secret'] }}</span></p>
                    </li>
                    <li>
                        Ingresá el código que muestra la app para confirmar:
                        <form method="POST" action="{{ route('admin.security.enable') }}" class="mt-3 flex flex-wrap items-start gap-2" data-once>
                            @csrf
                            <x-field name="code" label="Código de 6 dígitos" required inputmode="numeric" autocomplete="one-time-code" maxlength="7" class="w-48" />
                            <button class="btn btn-primary mt-6 !py-2" data-label="Activando…">Activar</button>
                        </form>
                    </li>
                </ol>
            @else
                <p class="mt-3 text-sm text-zinc-500">Activada el {{ $user->two_factor_confirmed_at->format('d/m/Y') }}. Te quedan {{ count($user->two_factor_recovery_codes ?? []) }} códigos de recuperación.</p>
                <form method="POST" action="{{ route('admin.security.codes') }}" class="mt-5 space-y-3 border-t border-zinc-100 pt-5 dark:border-ink-700">
                    @csrf
                    <x-field name="codes_password" label="Contraseña actual" type="password" required autocomplete="current-password" />
                    <button class="btn btn-secondary !py-2">Generar códigos de recuperación nuevos</button>
                </form>
                <form method="POST" action="{{ route('admin.security.disable') }}" class="mt-5 space-y-3 border-t border-zinc-100 pt-5 dark:border-ink-700"
                    data-confirm="¿Desactivar la verificación en dos pasos? El panel va a quedar protegido solo con la contraseña.">
                    @csrf @method('DELETE')
                    <x-field name="disable_password" label="Contraseña actual" type="password" required autocomplete="current-password" />
                    <button class="text-sm font-semibold text-red-600 hover:underline">Desactivar la verificación en dos pasos</button>
                </form>
            @endif
        </div>

        <div class="card p-6">
            <h2 class="font-sans text-base font-semibold">Cambiar contraseña</h2>
            <p class="mt-1 text-sm text-zinc-500">Mínimo 12 caracteres. Se rechazan las contraseñas que aparecieron en filtraciones conocidas. Al cambiarla se cierran las sesiones en otros dispositivos.</p>
            <form method="POST" action="{{ route('admin.security.password') }}" class="mt-5 space-y-4" data-once>
                @csrf @method('PUT')
                <x-field name="current_password" label="Contraseña actual" type="password" required autocomplete="current-password" />
                <x-field name="password" label="Contraseña nueva" type="password" required autocomplete="new-password" minlength="12" />
                <x-field name="password_confirmation" label="Repetir la contraseña nueva" type="password" required autocomplete="new-password" />
                <button class="btn btn-primary !py-2" data-label="Guardando…">Cambiar contraseña</button>
            </form>
        </div>
    </div>
@endsection
