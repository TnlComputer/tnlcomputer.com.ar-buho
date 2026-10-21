<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Ingresar · TNL Admin</title>
    <script nonce="{{ Vite::cspNonce() }}">try { if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && matchMedia('(prefers-color-scheme: dark)').matches)) document.documentElement.classList.add('dark') } catch {}</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="grid min-h-dvh place-items-center bg-zinc-50 p-4 dark:bg-ink-950">
    <form method="POST" action="{{ route('admin.login') }}" class="card w-full max-w-sm p-8" data-once>
        @csrf
        <p class="font-display text-xl font-bold">TNL<span class="text-brand-600 dark:text-brand-400">admin</span></p>
        <h1 class="mt-6 text-2xl font-bold">Ingresar</h1>
        <div class="mt-6 space-y-4">
            <x-field name="email" label="Email" type="email" required autofocus autocomplete="username" />
            <x-field name="password" label="Contraseña" type="password" required autocomplete="current-password" />
            <x-admin.checkbox name="remember" label="Recordarme" />
        </div>
        <button class="btn btn-primary mt-6 w-full" data-label="Ingresando…">Ingresar</button>
    </form>
</body>
</html>
