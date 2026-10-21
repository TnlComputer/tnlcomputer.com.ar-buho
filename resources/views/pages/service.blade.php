@extends('layouts.site')

@section('title', __('Service técnico'))
@section('description', __('Service técnico de PC y notebooks: no enciende, pantalla azul, se apaga, temperatura, se mojó. Pedí tu diagnóstico online.'))

@section('content')
    <x-page-header :eyebrow="__('Service técnico')" :title="__('¿Tu PC o notebook no anda bien?')" image="img/secciones/service-reparacion-1024.webp">
        {{ __('Contanos qué le pasa y te respondemos con los pasos a seguir. Reparamos PCs de escritorio y notebooks de todas las marcas.') }}
    </x-page-header>

    <section class="container-x grid gap-12 py-16 md:py-24 lg:grid-cols-[1fr_1.3fr]">
        <div>
            <h2 class="text-2xl font-bold">{{ __('Lo que hacemos') }}</h2>
            <ul class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                @foreach ([
                    ['cpu', __('Diagnóstico de hardware'), __('Fuente, mother, memoria, discos y placa de video.')],
                    ['shield', __('Virus y Windows'), __('Reinstalación, limpieza y recuperación del sistema.')],
                    ['sparkles', __('Limpieza y temperatura'), __('Limpieza interna y cambio de pasta térmica.')],
                    ['box', 'Upgrades', __('Pasamos tu equipo a SSD, sumamos memoria y más.')],
                    ['server', __('Redes'), __('Armado y configuración de redes para hogar y oficina.')],
                ] as [$icon, $title, $text])
                    <li class="card flex gap-4 p-4">
                        <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-900/30 dark:text-brand-300"><x-icon :name="$icon" /></span>
                        <div><p class="font-semibold text-zinc-900 dark:text-white">{{ $title }}</p><p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $text }}</p></div>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="card p-6 sm:p-8" id="formulario">
            <h2 class="text-2xl font-bold">{{ __('Pedí tu service') }}</h2>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">{{ __('Completá el formulario o escribinos por') }} <a href="{{ whatsapp_url(__('Hola, necesito un service para mi equipo.')) }}" target="_blank" rel="noopener" class="font-semibold text-brand-600 underline dark:text-brand-400">WhatsApp</a>.</p>

            <div class="mt-6">
                <x-form-status />
                <form method="POST" action="{{ lroute('service.send') }}#formulario" class="grid gap-5 sm:grid-cols-2" data-once>
                    @csrf
                    @include('partials.honeypot')
                    <x-field name="name" :label="__('Nombre')" required autocomplete="name" />
                    <x-field name="phone" :label="__('Teléfono / WhatsApp')" type="tel" required autocomplete="tel" />
                    <x-field name="email" label="Email" type="email" autocomplete="email" class="sm:col-span-2" />
                    <x-field name="equipment" :label="__('Equipo')" :placeholder="__('Ej: Notebook Lenovo IdeaPad 3')" />
                    <x-field name="subject" :label="__('¿Qué le pasa?')" type="select" required>
                        <option value="" disabled @selected(! old('subject'))>{{ __('Elegí una opción') }}</option>
                        @foreach (config('tnl.service_reasons') as $reason)
                            <option value="{{ $reason }}" @selected(old('subject') === $reason)>{{ __($reason) }}</option>
                        @endforeach
                    </x-field>
                    <x-field name="body" :label="__('Contanos un poco más')" type="textarea" required class="sm:col-span-2" :placeholder="__('¿Desde cuándo pasa? ¿Hiciste algún cambio?')" />
                    <div class="sm:col-span-2">
                        <button type="submit" class="btn btn-primary w-full sm:w-auto" data-label="{{ __('Enviando…') }}">{{ __('Enviar solicitud') }} <x-icon name="arrow-right" class="size-4" /></button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
