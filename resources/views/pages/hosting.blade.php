@extends('layouts.site')

@section('title', 'Hosting')
@section('description', __('Planes de hosting con cPanel o DirectAdmin, SSL gratis, backups automáticos y soporte. Alojamiento para tu sitio personal, profesional o institucional.'))

@section('content')
    <x-page-header eyebrow="Hosting" :title="__('Tu sitio online, rápido y seguro')" image="img/secciones/hosting-1-1024.webp">
        {{ __('Soluciones integrales para alojar tu sitio personal, profesional o institucional. Discos SSD, SSL gratis y soporte de personas reales.') }}
    </x-page-header>

    <section class="container-x py-16 md:py-24">
        @if ($plans->isEmpty())
            <p class="text-center text-zinc-500">{{ __('Estamos actualizando los planes.') }} <a href="{{ lroute('contact') }}" class="text-brand-600 underline">{{ __('Consultanos') }}</a>.</p>
        @else
            <div data-tabs>
                <div class="mx-auto flex w-fit rounded-xl border border-zinc-200 bg-zinc-100 p-1 dark:border-ink-700 dark:bg-ink-900" role="tablist" aria-label="{{ __('Panel de control') }}">
                    @foreach ($plans->keys() as $i => $panel)
                        <button type="button" role="tab" id="tab-{{ $panel }}" aria-controls="panel-{{ $panel }}" aria-selected="{{ $i === 0 ? 'true' : 'false' }}"
                                class="rounded-lg px-5 py-2 text-sm font-semibold text-zinc-600 transition aria-selected:bg-white aria-selected:text-zinc-950 aria-selected:shadow dark:text-zinc-400 dark:aria-selected:bg-ink-700 dark:aria-selected:text-white">
                            {{ \App\Models\HostingPlan::PANELS[$panel] ?? $panel }}
                        </button>
                    @endforeach
                </div>

                @foreach ($plans as $panel => $items)
                    <div id="panel-{{ $panel }}" role="tabpanel" aria-labelledby="tab-{{ $panel }}" @if (! $loop->first) hidden @endif
                         class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($items as $plan)
                            <article @class([
                                'card relative flex flex-col p-6',
                                'border-brand-500 ring-2 ring-brand-500/30 dark:border-brand-500' => $plan->highlighted,
                            ])>
                                @if ($plan->highlighted)
                                    <span class="absolute -top-3 left-6 rounded-full bg-brand-600 px-3 py-1 text-xs font-bold text-white">{{ __('Más elegido') }}</span>
                                @endif
                                <h2 class="text-xl font-bold">{{ __('Plan :name', ['name' => $plan->name]) }}</h2>
                                <p class="mt-1 text-sm text-zinc-500">{{ __('con :panel', ['panel' => $plan->panelName()]) }}</p>
                                <p class="mt-6 flex items-baseline gap-1">
                                    <span class="font-display text-4xl font-bold text-zinc-950 dark:text-white">{{ money($plan->price_monthly) }}</span>
                                    <span class="text-sm text-zinc-500">{{ __('/mes') }}</span>
                                </p>
                                <p class="text-xs text-zinc-500">{{ __('+ IVA (:amount final)', ['amount' => money($plan->price_monthly * 1.21)]) }}</p>
                                <ul class="mt-6 space-y-2.5 text-sm">
                                    @foreach ([
                                        trans_choice(':count dominio|:count dominios', $plan->domains),
                                        __(':size de espacio', ['size' => $plan->disk]),
                                        __(':size de transferencia', ['size' => $plan->transfer]),
                                        __('SSL gratis'), __('Casillas de mail ilimitadas'),
                                    ] as $feature)
                                        <li class="flex items-center gap-2.5"><x-icon name="check" class="size-4 text-brand-600 dark:text-brand-400" /> {{ $feature }}</li>
                                    @endforeach
                                </ul>
                                @if ($plan->price_semester || $plan->price_annual || $plan->price_biannual)
                                    <dl class="mt-6 space-y-1.5 rounded-xl bg-zinc-50 p-4 text-sm dark:bg-ink-800">
                                        @foreach (['price_semester' => __('Semestral'), 'price_annual' => __('Anual'), 'price_biannual' => __('Bianual')] as $field => $label)
                                            @if ($plan->{$field})
                                                <div class="flex justify-between"><dt class="text-zinc-500">{{ $label }}</dt><dd class="font-semibold">{{ money($plan->{$field}) }}<span class="font-normal text-zinc-500">{{ __('/mes') }}</span></dd></div>
                                            @endif
                                        @endforeach
                                    </dl>
                                @endif
                                <a href="{{ whatsapp_url(__('Hola, quiero contratar el Plan :plan de hosting con :panel.', ['plan' => $plan->name, 'panel' => $plan->panelName()])) }}" target="_blank" rel="noopener"
                                   @class(['btn mt-6 w-full', 'btn-primary' => $plan->highlighted, 'btn-secondary' => ! $plan->highlighted])>
                                    {{ __('Contratar') }}
                                </a>
                            </article>
                        @endforeach
                    </div>
                @endforeach
            </div>
            <p class="mt-6 text-center text-xs text-zinc-500">{{ __('Precios en pesos argentinos más IVA (21%), por mes. La renovación se mantiene al precio de lista vigente: sin promociones engañosas. Con pago semestral, anual o bianual se abona el período completo por adelantado.') }}</p>
        @endif
    </section>

    <section class="bg-zinc-50 py-16 md:py-24 dark:bg-ink-900/60">
        <div class="container-x">
            <div class="max-w-2xl">
                <p class="eyebrow">{{ __('Incluido en todos los planes') }}</p>
                <h2 class="mt-3 text-3xl font-bold sm:text-4xl">{{ __('Todo lo que necesitás, sin extras') }}</h2>
            </div>
            <div class="mt-10 grid gap-x-8 gap-y-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach (config('tnl.hosting_features') as [$title, $text])
                    <div class="flex gap-4">
                        <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-white text-brand-600 shadow-sm dark:bg-ink-800 dark:text-brand-400"><x-icon name="check" /></span>
                        <div>
                            <h3 class="font-sans font-semibold">{{ __($title) }}</h3>
                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ __($text) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @include('partials.cta')
@endsection
