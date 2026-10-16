@php
    $nav = [
        [__('Tienda'), 'shop.index', 'shop.*'],
        ['Hosting', 'hosting', 'hosting'],
        [__('Diseño web'), 'design', 'design'],
        ['Service', 'service', 'service'],
        ['Portfolio', 'portfolio', 'portfolio'],
        [__('Contacto'), 'contact', 'contact'],
    ];
    $title = trim($__env->yieldContent('title'));
    $description = trim($__env->yieldContent('description')) ?: __(config('tnl.tagline'));
    $socialIcon = fn ($label) => strtolower($label) === 'x' ? 'x-logo' : strtolower($label);
    $hello = __('Hola TNL Computer, quería hacer una consulta.');
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title.' · ' : '' }}TNL Computer</title>
    <meta name="description" content="{{ $description }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="alternate" hreflang="es" href="{{ locale_url('es') }}">
    <link rel="alternate" hreflang="en" href="{{ locale_url('en') }}">
    <link rel="alternate" hreflang="x-default" href="{{ locale_url('es') }}">
    <meta name="theme-color" content="#a8338f">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="TNL Computer">
    <meta property="og:locale" content="{{ is_en() ? 'en_US' : 'es_AR' }}">
    <meta property="og:title" content="{{ $title ?: 'TNL Computer' }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="@yield('og_image', asset('logo512.png'))">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@tnlcomputer">

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('logo192.png') }}">

    <script>
        // Aplica el tema antes de pintar para evitar el parpadeo.
        try {
            const t = localStorage.getItem('theme');
            if (t === 'dark' || (!t && matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        } catch {}
    </script>
    @if (route_is('home'))
        {{-- Datos del negocio para Google (los mismos que el Perfil de Empresa). El array va en un bloque PHP porque Blade toma la clave de contexto de schema.org como directiva. --}}
        @php
            $business = [
                '@context' => 'https://schema.org',
                '@type' => 'ComputerStore',
                'name' => config('tnl.name'),
                'url' => url('/'),
                'logo' => asset('logo512.png'),
                'image' => asset('logo512.png'),
                'description' => __(config('tnl.tagline')),
                'telephone' => '+54 11 4915-6462',
                'email' => config('tnl.email'),
                'areaServed' => 'Buenos Aires, Argentina',
                'sameAs' => array_values(array_diff_key(config('tnl.social'), ['LinkedIn' => 1, 'GitHub' => 1])),
            ];
        @endphp
        <script type="application/ld+json">{!! json_encode($business, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="flex min-h-dvh flex-col">
    <a href="#contenido" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 btn btn-primary">{{ __('Saltar al contenido') }}</a>

    <header class="sticky top-0 z-40 border-b border-zinc-200/70 bg-white/80 backdrop-blur-lg dark:border-ink-700/70 dark:bg-ink-950/80">
        <div class="container-x flex h-16 items-center gap-4">
            <a href="{{ lroute('home') }}" class="flex shrink-0 items-center gap-2.5" aria-label="TNL Computer, {{ __('inicio') }}">
                <img src="{{ asset('img/portfolio/logotnl-42x45.webp') }}" alt="" width="34" height="36" class="size-9 rounded-md object-contain">
                <span class="font-display text-lg font-bold tracking-tight text-zinc-950 dark:text-white">TNL<span class="text-brand-600 dark:text-brand-400">computer</span></span>
            </a>

            <nav class="ml-6 hidden items-center gap-1 lg:flex" aria-label="{{ __('Principal') }}">
                @foreach ($nav as [$label, $route, $pattern])
                    <a href="{{ lroute($route) }}"
                       @class([
                           'rounded-lg px-3 py-2 text-sm font-medium transition',
                           'text-brand-700 bg-brand-50 dark:bg-brand-900/30 dark:text-brand-300' => route_is($pattern),
                           'text-zinc-600 hover:text-zinc-950 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:text-white dark:hover:bg-ink-800' => ! route_is($pattern),
                       ])
                       @if (route_is($pattern)) aria-current="page" @endif>{{ $label }}</a>
                @endforeach
            </nav>

            <div class="ml-auto flex items-center gap-1">
                {{-- Idioma: el actual queda resaltado; el otro es el link para cambiar. --}}
                <div class="flex h-9 items-center rounded-lg border border-zinc-200 p-0.5 text-xs font-bold dark:border-ink-700" role="group" aria-label="{{ __('Idioma') }}">
                    @foreach (['es' => 'ES', 'en' => 'EN'] as $code => $label)
                        @if (app()->getLocale() === $code)
                            <span class="grid h-full place-items-center rounded-md bg-brand-600 px-2 text-white" aria-current="true" title="{{ $code === 'es' ? 'Español' : 'English' }}">{{ $label }}</span>
                        @else
                            <a href="{{ locale_url($code) }}" hreflang="{{ $code }}" lang="{{ $code }}" class="grid h-full place-items-center rounded-md px-2 text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-ink-800"
                               title="{{ $code === 'es' ? 'Ver en español' : 'View in English' }}">{{ $label }}</a>
                        @endif
                    @endforeach
                </div>
                <button type="button" data-theme-toggle class="grid size-10 place-items-center rounded-lg text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-ink-800" aria-label="{{ __('Cambiar tema claro/oscuro') }}">
                    <x-icon name="moon" class="dark:hidden" />
                    <x-icon name="sun" class="hidden dark:block" />
                </button>
                <a href="{{ lroute('cart.index') }}" class="relative grid size-10 place-items-center rounded-lg text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-ink-800" aria-label="{{ __('Carrito (:count productos)', ['count' => $cartCount]) }}">
                    <x-icon name="cart" />
                    @if ($cartCount)
                        <span class="absolute top-1 right-0.5 grid min-w-4.5 place-items-center rounded-full bg-brand-600 px-1 text-[10px] leading-4.5 font-bold text-white">{{ $cartCount }}</span>
                    @endif
                </a>
                <a href="{{ whatsapp_url($hello) }}" target="_blank" rel="noopener" class="btn btn-primary ml-2 hidden !py-2 sm:inline-flex">
                    <x-icon name="whatsapp" class="size-4" /> {{ __('Escribinos') }}
                </a>
                <button type="button" data-menu-toggle aria-controls="mobile-menu" aria-expanded="false" class="grid size-10 place-items-center rounded-lg text-zinc-700 hover:bg-zinc-100 lg:hidden dark:text-zinc-300 dark:hover:bg-ink-800" aria-label="{{ __('Abrir menú') }}">
                    <x-icon name="menu" />
                </button>
            </div>
        </div>

        <div id="mobile-menu" class="hidden h-[calc(100dvh-4rem)] overflow-y-auto border-t border-zinc-200 bg-white lg:hidden dark:border-ink-700 dark:bg-ink-950">
            <nav class="container-x flex flex-col gap-1 py-4" aria-label="{{ __('Menú mobile') }}">
                <a href="{{ lroute('home') }}" class="rounded-xl px-4 py-3 text-lg font-medium hover:bg-zinc-100 dark:hover:bg-ink-800">{{ __('Inicio') }}</a>
                @foreach ($nav as [$label, $route, $pattern])
                    <a href="{{ lroute($route) }}" @class(['rounded-xl px-4 py-3 text-lg font-medium hover:bg-zinc-100 dark:hover:bg-ink-800', 'text-brand-600 dark:text-brand-400' => route_is($pattern)])>{{ $label }}</a>
                @endforeach
                <a href="{{ whatsapp_url($hello) }}" target="_blank" rel="noopener" class="btn btn-whatsapp mt-4">
                    <x-icon name="whatsapp" /> {{ __('Escribinos por WhatsApp') }}
                </a>
            </nav>
        </div>
    </header>

    @if (session('cart_added'))
        <div class="container-x mt-4" role="status">
            <div class="flex flex-wrap items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-200">
                <x-icon name="check" class="size-4 shrink-0" />
                <span class="min-w-0 flex-1">{!! __(':product se agregó al carrito.', ['product' => '<strong>'.e(session('cart_added')).'</strong>']) !!}</span>
                <a href="{{ lroute('cart.index') }}" class="font-semibold underline underline-offset-2">{{ __('Ver carrito') }}</a>
            </div>
        </div>
    @endif

    <main id="contenido" class="flex-1">
        @yield('content')
    </main>

    <footer class="mt-24 border-t border-zinc-200 bg-zinc-50 dark:border-ink-700 dark:bg-ink-900">
        <div class="container-x grid gap-10 py-14 md:grid-cols-2 lg:grid-cols-4">
            <div class="lg:col-span-2">
                <a href="{{ lroute('home') }}" class="flex items-center gap-2.5">
                    <img src="{{ asset('img/portfolio/logotnl-42x45.webp') }}" alt="" width="34" height="36" class="size-9 rounded-md object-contain">
                    <span class="font-display text-lg font-bold text-zinc-950 dark:text-white">TNL<span class="text-brand-600 dark:text-brand-400">computer</span></span>
                </a>
                <p class="mt-4 max-w-sm text-sm text-zinc-600 dark:text-zinc-400">{{ __(config('tnl.tagline')) }} {{ __('Más de 30 años acompañando a empresas en Buenos Aires.') }}</p>
                <div class="mt-5 flex gap-2">
                    @foreach (config('tnl.social') as $label => $url)
                        <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $label }}" class="grid size-10 place-items-center rounded-lg text-zinc-500 hover:bg-white hover:text-brand-600 dark:hover:bg-ink-800 dark:hover:text-brand-400">
                            <x-icon :name="$socialIcon($label)" class="size-4.5" />
                        </a>
                    @endforeach
                </div>
            </div>
            <div>
                <h2 class="text-sm font-semibold">{{ __('Servicios') }}</h2>
                <ul class="mt-4 space-y-2.5 text-sm text-zinc-600 dark:text-zinc-400">
                    @foreach ($nav as [$label, $route])
                        <li><a href="{{ lroute($route) }}" class="hover:text-brand-600 dark:hover:text-brand-400">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h2 class="text-sm font-semibold">{{ __('Contacto') }}</h2>
                <ul class="mt-4 space-y-3 text-sm text-zinc-600 dark:text-zinc-400">
                    <li><a href="mailto:{{ config('tnl.email') }}" class="flex items-center gap-2 hover:text-brand-600 dark:hover:text-brand-400"><x-icon name="mail" class="size-4" /> {{ config('tnl.email') }}</a></li>
                    <li><a href="{{ whatsapp_url() }}" target="_blank" rel="noopener" class="flex items-center gap-2 hover:text-brand-600 dark:hover:text-brand-400"><x-icon name="whatsapp" class="size-4" /> {{ config('tnl.whatsapp_label') }}</a></li>
                    @if (config('tnl.google_review'))
                        <li><a href="{{ config('tnl.google_review') }}" target="_blank" rel="noopener" class="flex items-center gap-2 hover:text-brand-600 dark:hover:text-brand-400"><x-icon name="star" class="size-4" /> {{ __('Dejanos tu reseña en Google') }}</a></li>
                    @endif
                    <li>
                        <a href="{{ locale_url(is_en() ? 'es' : 'en') }}" hreflang="{{ is_en() ? 'es' : 'en' }}" class="flex items-center gap-2 hover:text-brand-600 dark:hover:text-brand-400">
                            <x-icon name="sparkles" class="size-4" /> {{ is_en() ? 'View site in Spanish' : 'Ver sitio en inglés' }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="border-t border-zinc-200 dark:border-ink-700">
            <div class="container-x flex flex-col gap-2 py-6 text-xs text-zinc-500 sm:flex-row sm:justify-between">
                <p>© {{ date('Y') }} TNL Computer. {{ __('Todos los derechos reservados.') }}</p>
                <p>{{ __('Precios en pesos argentinos, sujetos a cambio sin previo aviso.') }}</p>
            </div>
        </div>
    </footer>

    <a href="{{ whatsapp_url($hello) }}" target="_blank" rel="noopener"
       class="fixed right-4 bottom-4 z-30 grid size-14 place-items-center rounded-full bg-[#25d366] text-white shadow-xl shadow-black/20 transition hover:scale-105 sm:hidden"
       aria-label="{{ __('Escribinos por WhatsApp') }}">
        <x-icon name="whatsapp" class="size-7" />
    </a>
</body>
</html>
