@php
    $unread = \App\Models\Message::whereNull('read_at')->count();
    $toShip = \App\Models\Order::where('status', 'paid')->count();
    $menu = [
        ['Inicio', 'admin.dashboard', 'home', 'admin.dashboard'],
        ['Pedidos', 'admin.orders.index', 'cart', 'admin.orders.*'],
        ['Productos', 'admin.products.index', 'box', 'admin.products.*'],
        ['Rubros', 'admin.categories.index', 'tag', 'admin.categories.*'],
        ['Zonas de envío', 'admin.zones.index', 'truck', 'admin.zones.*'],
        ['Hosting', 'admin.plans.index', 'server', 'admin.plans.*'],
        ['Proyectos', 'admin.projects.index', 'briefcase', 'admin.projects.*'],
        ['Skills', 'admin.skills.index', 'code', 'admin.skills.*'],
        ['Certificaciones', 'admin.certifications.index', 'star', 'admin.certifications.*'],
        ['Mensajes', 'admin.messages.index', 'inbox', 'admin.messages.*'],
    ];
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title', 'Panel') · TNL Admin</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <script>try { if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && matchMedia('(prefers-color-scheme: dark)').matches)) document.documentElement.classList.add('dark') } catch {}</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-dvh bg-zinc-50 dark:bg-ink-950">
    <div class="lg:grid lg:min-h-dvh lg:grid-cols-[240px_1fr]">
        <aside class="border-b border-zinc-200 bg-white lg:border-r lg:border-b-0 dark:border-ink-700 dark:bg-ink-900">
            <div class="flex h-16 items-center justify-between px-4 lg:px-5">
                <a href="{{ route('admin.dashboard') }}" class="font-display text-lg font-bold">TNL<span class="text-brand-600 dark:text-brand-400">admin</span></a>
                <button type="button" data-menu-toggle aria-controls="mobile-menu" aria-expanded="false" class="grid size-10 place-items-center rounded-lg lg:hidden" aria-label="Menú"><x-icon name="menu" /></button>
            </div>
            <nav id="mobile-menu" class="hidden space-y-0.5 px-3 pb-4 lg:block" aria-label="Panel">
                @foreach ($menu as [$label, $route, $icon, $pattern])
                    <a href="{{ route($route) }}" @class([
                        'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium',
                        'bg-brand-50 text-brand-700 dark:bg-brand-900/30 dark:text-brand-300' => request()->routeIs($pattern),
                        'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-ink-800' => ! request()->routeIs($pattern),
                    ])>
                        <x-icon :name="$icon" class="size-4.5" /> {{ $label }}
                        @if ($route === 'admin.orders.index' && $toShip)
                            <span class="ml-auto rounded-full bg-emerald-600 px-2 text-xs leading-5 font-bold text-white" title="Pagados para preparar">{{ $toShip }}</span>
                        @endif
                        @if ($route === 'admin.messages.index' && $unread)
                            <span class="ml-auto rounded-full bg-brand-600 px-2 text-xs leading-5 font-bold text-white">{{ $unread }}</span>
                        @endif
                    </a>
                @endforeach
                <div class="my-3 border-t border-zinc-200 dark:border-ink-700"></div>
                <a href="{{ route('home') }}" target="_blank" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-ink-800"><x-icon name="external" class="size-4.5" /> Ver sitio</a>
                <button type="button" data-theme-toggle class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-ink-800"><x-icon name="moon" class="size-4.5" /> Tema</button>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-ink-800"><x-icon name="logout" class="size-4.5" /> Salir</button>
                </form>
            </nav>
        </aside>

        <main class="min-w-0 p-4 sm:p-6 lg:p-10">
            <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
                <h1 class="text-2xl font-bold sm:text-3xl">@yield('title')</h1>
                @yield('actions')
            </div>

            @if (session('status'))
                <div class="mb-6 flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-200" role="status">
                    <x-icon name="check" class="size-4" /> {{ session('status') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
