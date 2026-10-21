<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Verificación · TNL Admin</title>
    <script nonce="{{ Vite::cspNonce() }}">try { if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && matchMedia('(prefers-color-scheme: dark)').matches)) document.documentElement.classList.add('dark') } catch {}</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="grid min-h-dvh place-items-center bg-zinc-50 p-4 dark:bg-ink-950">
    <div class="card w-full max-w-sm p-8">
        <p class="font-display text-xl font-bold">TNL<span class="text-brand-600 dark:text-brand-400">admin</span></p>
        <h1 class="mt-6 text-2xl font-bold">Verificación en dos pasos</h1>
        <p class="mt-2 text-sm text-zinc-500">Ingresá el código de 6 dígitos que muestra la app de autenticación del celular.</p>

        <form method="POST" action="{{ route('admin.two-factor') }}" class="mt-6" data-once>
            @csrf
            <x-field name="code" label="Código" required autofocus inputmode="numeric" autocomplete="one-time-code" maxlength="7" pattern="[0-9 ]*" />
            <button class="btn btn-primary mt-6 w-full" data-label="Verificando…">Ingresar</button>
        </form>

        <details class="mt-6 border-t border-zinc-100 pt-4 text-sm dark:border-ink-700">
            <summary class="cursor-pointer text-zinc-500">¿No tenés el celular? Usá un código de recuperación</summary>
            <form method="POST" action="{{ route('admin.two-factor') }}" class="mt-4" data-once>
                @csrf
                <x-field name="recovery_code" label="Código de recuperación" required autocomplete="off" placeholder="xxxxx-xxxxx" hint="Cada código sirve una sola vez." />
                <button class="btn btn-secondary mt-4 w-full" data-label="Verificando…">Ingresar</button>
            </form>
        </details>

        <a href="{{ route('admin.login') }}" class="mt-6 block text-center text-sm text-brand-600 hover:underline">Volver</a>
    </div>
</body>
</html>
