@extends('layouts.site')

@php $p = config('tnl.profile'); @endphp

@section('title', $p['name'].' · '.$p['role'])
@section('description', __('Portfolio de :name, :role: desarrollo web, APIs, datos e inteligencia artificial aplicada.', ['name' => $p['name'], 'role' => $p['role']]))
@section('og_image', asset($p['photo']))

@section('content')
    {{-- Hero --}}
    <section class="relative isolate overflow-hidden">
        <div class="absolute inset-0 -z-10 bg-[radial-gradient(50%_70%_at_90%_10%,var(--color-brand-100),transparent)] dark:bg-[radial-gradient(50%_70%_at_90%_10%,color-mix(in_oklab,var(--color-brand-800)_50%,transparent),transparent)]"></div>
        <div class="container-x grid items-center gap-12 py-16 md:py-24 lg:grid-cols-[1.4fr_1fr]">
            <div>
                <p class="eyebrow">{{ $p['name'] }}</p>
                <h1 class="mt-3 text-5xl font-bold sm:text-6xl">{{ $p['role'] }}</h1>
                <p class="mt-6 max-w-xl text-xl text-zinc-600 dark:text-zinc-400">{{ __($p['lead']) }}</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#proyectos" class="btn btn-primary">{{ __('Ver proyectos') }}</a>
                    <a href="{{ lroute('portfolio.pdf') }}" class="btn btn-secondary" download><x-icon name="download" class="size-4" /> {{ __('Descargar PDF') }}</a>
                    <a href="{{ $p['linkedin'] }}" target="_blank" rel="noopener" class="btn btn-secondary"><x-icon name="linkedin" class="size-4" /> LinkedIn</a>
                    <a href="{{ $p['github'] }}" target="_blank" rel="noopener" class="btn btn-secondary"><x-icon name="github" class="size-4" /> GitHub</a>
                </div>
            </div>
            <div class="relative mx-auto w-full max-w-sm">
                <div class="absolute -inset-3 -z-10 rounded-[2.5rem] bg-gradient-to-br from-brand-500/30 to-sky-400/30 blur-2xl"></div>
                <img src="{{ asset($p['photo']) }}" alt="{{ __('Foto de :name', ['name' => $p['name']]) }}" width="480" height="560" class="aspect-[6/7] w-full rounded-[2rem] object-cover shadow-2xl" fetchpriority="high">
            </div>
        </div>
    </section>

    {{-- Sobre mí + stack --}}
    <section class="container-x grid gap-12 py-16 md:py-24 lg:grid-cols-2" id="sobre-mi">
        <div>
            <p class="eyebrow">{{ __('Sobre mí') }}</p>
            <h2 class="mt-3 text-3xl font-bold sm:text-4xl">{{ __($p['about_title']) }}</h2>
            <div class="prose-tnl mt-6 text-lg text-zinc-600 dark:text-zinc-400">
                @foreach ($p['about'] as $paragraph)<p>{{ __($paragraph) }}</p>@endforeach
            </div>
        </div>
        <div class="space-y-6">
            @foreach ($skills as $group => $items)
                <div>
                    <h3 class="font-sans text-sm font-semibold text-zinc-500 uppercase">{{ __($group) }}</h3>
                    <ul class="mt-3 flex flex-wrap gap-2">
                        @foreach ($items as $skill)
                            <li @class([
                                'rounded-full px-3.5 py-1.5 text-sm font-medium',
                                'bg-brand-600 text-white' => $loop->parent->first,
                                'bg-zinc-100 text-zinc-700 dark:bg-ink-800 dark:text-zinc-300' => ! $loop->parent->first,
                            ])>{{ __($skill->name) }}</li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Experiencia --}}
    <section class="bg-zinc-50 py-16 md:py-24 dark:bg-ink-900/60" id="experiencia">
        <div class="container-x">
            <p class="eyebrow">{{ __('Experiencia') }}</p>
            <h2 class="mt-3 text-3xl font-bold sm:text-4xl">{{ __('Roles y trayectoria') }}</h2>
            <ol class="mt-12 space-y-0 border-l-2 border-brand-200 dark:border-brand-900">
                @foreach ($p['experience'] as [$period, $role, $text])
                    <li class="relative pb-10 pl-8 last:pb-0">
                        <span class="absolute top-1.5 -left-[9px] size-4 rounded-full border-4 border-zinc-50 bg-brand-600 dark:border-ink-900"></span>
                        <p class="text-sm font-semibold text-brand-600 dark:text-brand-400">{{ __($period) }}</p>
                        <h3 class="mt-1 text-xl font-semibold">{{ __($role) }}</h3>
                        <p class="mt-2 max-w-2xl text-zinc-600 dark:text-zinc-400">{{ __($text) }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Proyectos --}}
    <section class="container-x py-16 md:py-24" id="proyectos">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="eyebrow">{{ __('Proyectos') }}</p>
                <h2 class="mt-3 text-3xl font-bold sm:text-4xl">{{ __('Trabajo seleccionado') }}</h2>
                <p class="mt-3 text-zinc-600 dark:text-zinc-400">{{ __('Una selección de sistemas para clientes y proyectos recientes. El resto está en GitHub.') }}</p>
            </div>
            <a href="{{ $p['github'] }}" target="_blank" rel="noopener" class="btn btn-secondary"><x-icon name="github" class="size-4" /> {{ __('Ver todos en GitHub') }}</a>
        </div>
        <div class="mt-10 grid items-start gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($projects as $project)
                @include('partials.project-card', ['project' => $project])
            @endforeach
        </div>
    </section>

    {{-- Certificaciones --}}
    @if ($certifications->isNotEmpty())
        <section class="bg-zinc-50 py-16 md:py-24 dark:bg-ink-900/60" id="certificaciones">
            <div class="container-x">
                <p class="eyebrow">{{ __('Certificaciones') }}</p>
                <h2 class="mt-3 text-3xl font-bold sm:text-4xl">{{ __('Formación continua') }}</h2>
                <p class="mt-3 text-zinc-600 dark:text-zinc-400">
                    {{ __(':count certificados, con su respaldo para descargar.', ['count' => $certifications->count()]) }}
                    @if (is_en()) <span class="text-sm">(Certificate titles are shown as issued, in Spanish.)</span>@endif
                </p>

                @php $featuredCerts = $certifications->where('featured', true)->take(6); @endphp
                @if ($featuredCerts->isNotEmpty())
                    <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($featuredCerts as $cert)
                            <a href="{{ $cert->fileUrl() }}" target="_blank" rel="noopener" class="card group flex gap-4 p-5 transition hover:border-brand-400">
                                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-900/30 dark:text-brand-300"><x-icon name="star" /></span>
                                <div class="min-w-0">
                                    <p class="font-semibold text-zinc-900 group-hover:text-brand-700 dark:text-white dark:group-hover:text-brand-300" lang="es">{{ $cert->title }}</p>
                                    <p class="mt-1 text-sm text-zinc-500">{{ $cert->issuer }}@if ($cert->issued_at) · {{ $cert->issued_at->translatedFormat('M Y') }}@endif</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif

                <div class="mt-10" data-cert-filter>
                    <div class="flex flex-wrap gap-2" role="group" aria-label="{{ __('Filtrar por emisor') }}">
                        <button type="button" data-issuer="" aria-pressed="true" class="chip !px-3.5 !py-1.5 aria-pressed:bg-brand-600 aria-pressed:text-white">{{ __('Todos') }} ({{ $certifications->count() }})</button>
                        @foreach ($issuers as $issuer => $count)
                            <button type="button" data-issuer="{{ $issuer }}" aria-pressed="false" class="chip !px-3.5 !py-1.5 aria-pressed:bg-brand-600 aria-pressed:text-white">{{ $issuer }} ({{ $count }})</button>
                        @endforeach
                    </div>
                    {{-- Agrupadas por año; solo el más reciente arranca abierto. --}}
                    <div class="mt-6 space-y-3">
                        @foreach ($certifications->groupBy(fn ($c) => $c->issued_at?->year ?? __('Sin fecha')) as $year => $certs)
                            <details class="card group/year overflow-hidden" data-cert-year @if ($loop->first) open @endif>
                                <summary class="flex cursor-pointer list-none items-center gap-3 px-5 py-4 select-none hover:bg-zinc-50 dark:hover:bg-ink-800 [&::-webkit-details-marker]:hidden">
                                    <span class="font-display text-xl font-bold text-zinc-900 dark:text-white">{{ $year }}</span>
                                    <span class="chip" data-year-count>{{ trans_choice(':count certificado|:count certificados', $certs->count()) }}</span>
                                    <x-icon name="arrow-right" class="ml-auto size-4 text-zinc-400 transition group-open/year:rotate-90" />
                                </summary>
                                <ul class="divide-y divide-zinc-100 border-t border-zinc-100 dark:divide-ink-700 dark:border-ink-700">
                                    @foreach ($certs as $cert)
                                        <li data-cert-issuer="{{ $cert->issuer }}" class="flex flex-wrap items-center gap-x-4 gap-y-1 px-5 py-3.5">
                                            <span class="w-20 shrink-0 text-sm text-zinc-500 tabular-nums">{{ $cert->issued_at?->format('m/Y') }}</span>
                                            <span class="min-w-0 flex-1 font-medium text-zinc-900 dark:text-zinc-100" lang="es">{{ $cert->title }}</span>
                                            <span class="chip">{{ $cert->issuer }}</span>
                                            @if ($cert->fileUrl())
                                                <a href="{{ $cert->fileUrl() }}" target="_blank" rel="noopener" class="text-sm font-semibold text-brand-600 hover:underline dark:text-brand-400" aria-label="{{ __('Ver certificado :title', ['title' => $cert->title]) }}">{{ __('Ver') }}</a>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </details>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- Contacto --}}
    <section class="container-x mt-20" id="contacto">
        <div class="relative isolate overflow-hidden rounded-[2rem] bg-ink-900 px-6 py-14 text-center sm:px-12">
            <div class="absolute inset-0 -z-10 bg-[radial-gradient(50%_80%_at_50%_0%,color-mix(in_oklab,var(--color-brand-600)_45%,transparent),transparent)]"></div>
            <h2 class="mx-auto max-w-2xl text-3xl font-bold text-white sm:text-4xl">{{ __('¿Conversamos sobre tu próximo proyecto?') }}</h2>
            <p class="mx-auto mt-4 max-w-xl text-zinc-300">{{ __('Disponible para oportunidades full-stack, colaboraciones y desarrollo de productos digitales.') }}</p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <a href="mailto:{{ $p['email'] }}" class="btn btn-primary"><x-icon name="mail" /> {{ $p['email'] }}</a>
                <a href="{{ $p['linkedin'] }}" target="_blank" rel="noopener" class="btn border border-white/20 text-white hover:bg-white/10"><x-icon name="linkedin" /> LinkedIn</a>
            </div>
        </div>
    </section>
@endsection
