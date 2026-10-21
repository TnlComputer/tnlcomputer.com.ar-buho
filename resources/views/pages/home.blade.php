@extends('layouts.site')

@section('description', __('TNL Computer: venta de hardware y notebooks, service técnico de PC y notebooks, hosting y desarrollo web a medida.'))

@section('content')
    {{-- Hero --}}
    <section class="relative isolate overflow-hidden">
        <div class="absolute inset-0 -z-10 bg-[radial-gradient(50%_60%_at_85%_10%,var(--color-brand-100),transparent),radial-gradient(40%_50%_at_0%_100%,color-mix(in_oklab,var(--color-sky-accent)_25%,transparent),transparent)] dark:bg-[radial-gradient(50%_60%_at_85%_10%,color-mix(in_oklab,var(--color-brand-800)_55%,transparent),transparent),radial-gradient(40%_50%_at_0%_100%,color-mix(in_oklab,var(--color-sky-accent)_12%,transparent),transparent)]"></div>
        <div class="container-x grid items-center gap-12 py-16 md:py-24 lg:grid-cols-[1.1fr_1fr]">
            <div>
                <p class="inline-flex items-center gap-2 rounded-full border border-brand-200 bg-white/70 px-3 py-1 text-xs font-semibold text-brand-700 dark:border-brand-800 dark:bg-ink-900/70 dark:text-brand-300">
                    <x-icon name="sparkles" class="size-3.5" /> {{ __('Más de 30 años en tecnología') }}
                </p>
                <h1 class="mt-5 text-4xl leading-[1.05] font-bold sm:text-6xl">
                    {{ __('Tecnología que') }} <span class="bg-gradient-to-r from-brand-600 to-sky-500 bg-clip-text text-transparent dark:from-brand-400 dark:to-sky-accent">{{ __('trabaja para vos') }}</span>
                </h1>
                <p class="mt-6 max-w-xl text-lg text-zinc-600 dark:text-zinc-400">
                    {{ __('Hardware, service técnico, hosting y desarrollo web en un solo lugar. Te asesoramos para que elijas bien y te acompañamos después.') }}
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ lroute('shop.index') }}" class="btn btn-primary">{{ __('Ver la tienda') }} <x-icon name="arrow-right" class="size-4" /></a>
                    <a href="{{ lroute('service') }}" class="btn btn-secondary"><x-icon name="wrench" class="size-4" /> {{ __('Pedir un service') }}</a>
                </div>
                <dl class="mt-12 grid max-w-lg grid-cols-3 gap-6 border-t border-zinc-200 pt-6 dark:border-ink-700">
                    <div><dt class="text-xs text-zinc-500">{{ __('Experiencia') }}</dt><dd class="font-display text-2xl font-bold">{{ __('+30 años') }}</dd></div>
                    <div><dt class="text-xs text-zinc-500">{{ __('Servicios') }}</dt><dd class="font-display text-2xl font-bold">360°</dd></div>
                    <div><dt class="text-xs text-zinc-500">{{ __('Atención') }}</dt><dd class="font-display text-2xl font-bold">{{ __('Directa') }}</dd></div>
                </dl>
            </div>
            <div class="relative">
                <div class="absolute -inset-4 -z-10 rounded-[2.5rem] bg-gradient-to-br from-brand-500/20 to-sky-400/20 blur-2xl"></div>
                {{-- Foto de Unsplash (licencia libre, sin atribución obligatoria). --}}
                <img src="{{ asset('img/secciones/pc-rgb-1024.webp') }}" alt="{{ __('Interior de una PC gamer con water cooler e iluminación RGB') }}" width="1024" height="768"
                     srcset="{{ asset('img/secciones/pc-rgb-640.webp') }} 640w, {{ asset('img/secciones/pc-rgb-1024.webp') }} 1024w" sizes="(min-width: 1024px) 560px, 100vw"
                     class="aspect-[4/3] w-full rounded-[2rem] object-cover shadow-2xl shadow-zinc-900/20" fetchpriority="high">
                <div class="card absolute -bottom-6 -left-4 hidden items-center gap-3 p-4 shadow-xl sm:flex">
                    <span class="grid size-11 place-items-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300"><x-icon name="shield" /></span>
                    <div>
                        <p class="text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Diagnóstico sin vueltas') }}</p>
                        <p class="text-xs text-zinc-500">{{ __('Te explicamos qué tiene tu equipo') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Marcas --}}
    <section class="border-y border-zinc-200 bg-zinc-50 py-6 dark:border-ink-700 dark:bg-ink-900" aria-label="{{ __('Marcas con las que trabajamos') }}">
        <div class="marquee overflow-hidden">
            <div class="marquee-track flex w-max gap-10">
                @foreach ([1, 2] as $copy)
                    @foreach (config('tnl.brands') as $brand)
                        <img src="{{ asset('img/marcas/btn'.($brand === 'redragon' ? 'redragon-1' : $brand).'.svg') }}" alt="{{ $copy === 1 ? ucfirst($brand) : '' }}"
                             @if ($copy === 2) aria-hidden="true" @endif
                             width="173" height="50" class="h-10 w-auto opacity-70 grayscale transition hover:opacity-100 hover:grayscale-0 dark:brightness-0 dark:invert dark:hover:opacity-100">
                    @endforeach
                @endforeach
            </div>
        </div>
    </section>

    {{-- Servicios --}}
    <section class="container-x py-20 md:py-28">
        <div class="max-w-2xl">
            <p class="eyebrow">{{ __('Qué hacemos') }}</p>
            <h2 class="mt-3 text-3xl font-bold sm:text-4xl">{{ __('Todo lo que tu equipo y tu web necesitan') }}</h2>
        </div>
        @php
            $services = [
                ['cpu', __('Hardware y notebooks'), __('Componentes, PCs armadas, periféricos y notebooks de las mejores marcas.'), 'shop.index', __('Ver productos')],
                ['wrench', __('Service técnico'), __('Reparación de PCs y notebooks, limpieza, upgrades, recuperación de Windows y más.'), 'service', __('Pedir service')],
                ['server', 'Hosting', __('Alojamiento con cPanel o DirectAdmin, SSL gratis, backups y soporte cercano.'), 'hosting', __('Ver planes')],
                ['code', __('Diseño y desarrollo web'), __('Sitios, tiendas y sistemas a medida con Laravel, PHP y JavaScript.'), 'design', __('Conocer más')],
            ];
        @endphp
        <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($services as [$icon, $title, $text, $route, $cta])
                <a href="{{ lroute($route) }}" class="group card flex flex-col p-6 transition hover:-translate-y-1 hover:border-brand-300 hover:shadow-xl hover:shadow-brand-900/5 dark:hover:border-brand-700">
                    <span class="grid size-12 place-items-center rounded-xl bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white dark:bg-brand-900/30 dark:text-brand-300">
                        <x-icon :name="$icon" class="size-6" />
                    </span>
                    <h3 class="mt-5 text-lg font-semibold">{{ $title }}</h3>
                    <p class="mt-2 flex-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $text }}</p>
                    <span class="mt-5 inline-flex items-center gap-1.5 text-sm font-semibold text-brand-600 dark:text-brand-400">{{ $cta }} <x-icon name="arrow-right" class="size-4 transition group-hover:translate-x-1" /></span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Ofertas --}}
    @if ($featured->isNotEmpty())
        <section class="bg-zinc-50 py-20 md:py-28 dark:bg-ink-900/60">
            <div class="container-x">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="eyebrow">{{ __('Destacados') }}</p>
                        <h2 class="mt-3 text-3xl font-bold sm:text-4xl">{{ __('Ofertas de la semana') }}</h2>
                    </div>
                    <a href="{{ lroute('shop.index') }}" class="btn btn-secondary">{{ __('Ver toda la tienda') }} <x-icon name="arrow-right" class="size-4" /></a>
                </div>
                <div class="mt-10 grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4 lg:gap-6">
                    @foreach ($featured as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Por qué TNL --}}
    <section class="container-x grid items-center gap-12 py-20 md:py-28 lg:grid-cols-2">
        <img src="{{ asset('img/secciones/service-1-1024.webp') }}" srcset="{{ asset('img/secciones/service-1-640.webp') }} 640w, {{ asset('img/secciones/service-1-1024.webp') }} 1024w" sizes="(min-width: 1024px) 600px, 100vw" alt="{{ __('Técnico reparando una computadora') }}" loading="lazy" width="1024" height="683" class="aspect-[4/3] w-full rounded-[2rem] object-cover">
        <div>
            <p class="eyebrow">{{ __('Por qué elegirnos') }}</p>
            <h2 class="mt-3 text-3xl font-bold sm:text-4xl">{{ __('Atención directa, de alguien que sabe') }}</h2>
            <p class="mt-5 text-lg text-zinc-600 dark:text-zinc-400">{{ __('Empezamos en los 80, cuando las computadoras recién llegaban a las oficinas. Desde entonces acompañamos a empresas y particulares con el mismo criterio: soluciones que funcionan y explicaciones claras.') }}</p>
            <ul class="mt-8 space-y-4">
                @foreach ([
                    [__('Asesoramiento honesto'), __('Te recomendamos lo que necesitás, no lo más caro.')],
                    [__('Hardware y software juntos'), __('Armamos, instalamos y configuramos tu equipo y tu red.')],
                    [__('Un solo contacto'), __('Tu web, tu hosting y tus equipos con el mismo equipo de soporte.')],
                ] as [$title, $text])
                    <li class="flex gap-4">
                        <span class="mt-0.5 grid size-7 shrink-0 place-items-center rounded-full bg-brand-600 text-white"><x-icon name="check" class="size-4" /></span>
                        <div><p class="font-semibold text-zinc-900 dark:text-white">{{ $title }}</p><p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $text }}</p></div>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- Portfolio --}}
    @if ($projects->isNotEmpty())
        <section class="container-x pb-8">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="eyebrow">Portfolio</p>
                    <h2 class="mt-3 text-3xl font-bold sm:text-4xl">{{ __('Algunos trabajos') }}</h2>
                </div>
                <a href="{{ lroute('portfolio') }}" class="btn btn-secondary">{{ __('Ver portfolio') }} <x-icon name="arrow-right" class="size-4" /></a>
            </div>
            <div class="mt-10 grid gap-6 md:grid-cols-3">
                @foreach ($projects as $project)
                    @include('partials.project-card', ['project' => $project])
                @endforeach
            </div>
        </section>
    @endif

    @include('partials.cta')
@endsection
