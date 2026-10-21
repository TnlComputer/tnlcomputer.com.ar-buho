@extends('layouts.site')

@section('title', __('Diseño y desarrollo web'))
@section('description', __('Diseño web, branding, marketing digital y desarrollo de sitios y sistemas a medida con Laravel, PHP y JavaScript.'))

@section('content')
    <x-page-header :eyebrow="__('Diseño y desarrollo web')" :title="__('Sitios que se ven bien y funcionan mejor')" image="img/secciones/diseno-web-1024.webp">
        {{ __('Analizamos, diseñamos y desarrollamos tu web según lo que necesita tu negocio: desde una landing hasta un sistema de gestión completo.') }}
        <x-slot:actions>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ lroute('contact') }}" class="btn btn-primary">{{ __('Pedir presupuesto') }} <x-icon name="arrow-right" class="size-4" /></a>
                <a href="{{ lroute('portfolio') }}" class="btn btn-secondary">{{ __('Ver trabajos') }}</a>
            </div>
        </x-slot:actions>
    </x-page-header>

    <section class="container-x py-16 md:py-24">
        <div class="max-w-2xl">
            <p class="eyebrow">{{ __('Servicios') }}</p>
            <h2 class="mt-3 text-3xl font-bold sm:text-4xl">{{ __('Un proceso completo, de punta a punta') }}</h2>
        </div>
        @php $icons = ['palette', 'sparkles', 'chart', 'chart', 'code', 'pen', 'smartphone', 'camera', 'life-buoy']; @endphp
        <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach (config('tnl.design_services') as $i => [$title, $text])
                <div class="card p-6">
                    <span class="grid size-11 place-items-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-900/30 dark:text-brand-300"><x-icon :name="$icons[$i] ?? 'check'" /></span>
                    <h3 class="mt-5 text-lg font-semibold">{{ __($title) }}</h3>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">{{ __($text) }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="bg-zinc-50 py-16 md:py-24 dark:bg-ink-900/60">
        <div class="container-x">
            <div class="max-w-2xl">
                <p class="eyebrow">{{ __('Cómo trabajamos') }}</p>
                <h2 class="mt-3 text-3xl font-bold sm:text-4xl">{{ __('De la idea al sitio publicado') }}</h2>
            </div>
            <ol class="mt-12 grid gap-8 md:grid-cols-4">
                @foreach ([
                    [__('Relevamiento'), __('Entendemos tu negocio, tu público y qué tiene que lograr el sitio.')],
                    [__('Diseño'), __('Armamos la estructura y el diseño visual para que lo apruebes.')],
                    [__('Desarrollo'), __('Programamos, cargamos contenidos y probamos en todos los dispositivos.')],
                    [__('Publicación y soporte'), __('Lo publicamos en tu hosting y seguimos acompañándote.')],
                ] as $i => [$title, $text])
                    <li>
                        <span class="font-display text-5xl font-bold text-brand-200 dark:text-brand-900">0{{ $i + 1 }}</span>
                        <h3 class="mt-2 text-lg font-semibold">{{ $title }}</h3>
                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">{{ $text }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    @if ($projects->isNotEmpty())
        <section class="container-x pt-16 md:pt-24">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <h2 class="text-3xl font-bold sm:text-4xl">{{ __('Trabajos recientes') }}</h2>
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
