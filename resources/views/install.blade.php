<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Instalación · TNL Computer</title>
    <style>
        body { font: 15px/1.5 system-ui, sans-serif; background: #f4f4f5; color: #18181b; margin: 0; padding: 32px 16px; }
        main { max-width: 640px; margin: 0 auto; background: #fff; border-radius: 16px; padding: 28px; box-shadow: 0 1px 3px #0001; }
        h1 { margin: 0 0 16px; font-size: 22px; }
        li { margin: 4px 0; } .ok { color: #047857; } .bad { color: #b91c1c; font-weight: 600; }
        label { display: block; margin-top: 12px; font-weight: 600; font-size: 14px; }
        input { width: 100%; box-sizing: border-box; padding: 10px; border: 1px solid #d4d4d8; border-radius: 8px; font: inherit; }
        button { margin-top: 20px; background: #a8338f; color: #fff; border: 0; border-radius: 10px; padding: 12px 20px; font: inherit; font-weight: 600; cursor: pointer; }
        .alert { padding: 12px; border-radius: 10px; margin: 12px 0; } .alert.err { background: #fef2f2; color: #991b1b; } .alert.good { background: #ecfdf5; color: #065f46; }
    </style>
</head>
<body>
<main>
    <h1>Instalación del sitio</h1>

    @isset($error)<div class="alert err">{{ $error }}</div>@endisset
    @if ($errors->any())<div class="alert err">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

    @isset($log)
        <ul>@foreach ($log as $line)<li>{{ $line }}</li>@endforeach</ul>
    @endisset

    @if (! empty($done))
        <div class="alert good">Listo. El sitio quedó instalado y este instalador ya no funciona.<br>
            Borrá la línea <code>INSTALL_TOKEN</code> del <code>.env</code> y entrá al panel en <a href="{{ url('/admin') }}">/admin</a>.</div>
    @else
        <h2 style="font-size:16px">Requisitos</h2>
        <ul>
            @foreach ($checks as [$label, $ok])
                <li class="{{ $ok ? 'ok' : 'bad' }}">{{ $ok ? '✔' : '✘' }} {{ $label }}</li>
            @endforeach
        </ul>

        <form method="POST" action="{{ url('/instalar') }}">
            @csrf
            <label for="token">Clave de instalación (INSTALL_TOKEN del .env)</label>
            <input id="token" name="token" type="password" required autocomplete="off">
            <label for="name">Tu nombre</label>
            <input id="name" name="name" value="{{ old('name', 'Jorge') }}" required>
            <label for="email">Email del administrador</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required>
            <label for="password">Contraseña del panel (mínimo 10 caracteres)</label>
            <input id="password" name="password" type="password" required autocomplete="new-password">
            <label for="password_confirmation">Repetir contraseña</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
            <button type="submit">Instalar</button>
        </form>
    @endif
</main>
</body>
</html>
