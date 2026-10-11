@extends('layouts.site')

@section('title', __('Contacto'))
@section('description', __('Contactá a TNL Computer por WhatsApp, email o desde el formulario. Te respondemos a la brevedad.'))

@section('content')
    <x-page-header :eyebrow="__('Contacto')" :title="__('Hablemos')">
        {{ __('Consultas, presupuestos o lo que necesites. Elegí el canal que te quede más cómodo.') }}
    </x-page-header>

    <section class="container-x grid gap-12 py-16 md:py-24 lg:grid-cols-[1fr_1.4fr]">
        <div class="space-y-4">
            <a href="{{ whatsapp_url(__('Hola TNL Computer, quería hacer una consulta.')) }}" target="_blank" rel="noopener" class="card flex items-center gap-4 p-5 transition hover:border-[#25d366]">
                <span class="grid size-12 place-items-center rounded-xl bg-[#25d366]/15 text-[#128c4a] dark:text-[#25d366]"><x-icon name="whatsapp" class="size-6" /></span>
                <div><p class="font-semibold text-zinc-900 dark:text-white">WhatsApp</p><p class="text-sm text-zinc-600 dark:text-zinc-400">{{ config('tnl.whatsapp_label') }}</p></div>
                <x-icon name="arrow-right" class="ml-auto size-4 text-zinc-400" />
            </a>
            <a href="mailto:{{ config('tnl.email') }}" class="card flex items-center gap-4 p-5 transition hover:border-brand-400">
                <span class="grid size-12 place-items-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-900/30 dark:text-brand-300"><x-icon name="mail" class="size-6" /></span>
                <div><p class="font-semibold text-zinc-900 dark:text-white">Email</p><p class="text-sm text-zinc-600 dark:text-zinc-400">{{ config('tnl.email') }}</p></div>
                <x-icon name="arrow-right" class="ml-auto size-4 text-zinc-400" />
            </a>
            <div class="card p-5">
                <p class="font-semibold text-zinc-900 dark:text-white">{{ __('Redes') }}</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach (config('tnl.social') as $label => $url)
                        <a href="{{ $url }}" target="_blank" rel="noopener" class="chip gap-1.5 !py-1.5 hover:text-brand-600">
                            <x-icon :name="strtolower($label) === 'x' ? 'x-logo' : strtolower($label)" class="size-3.5" /> {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card p-6 sm:p-8" id="formulario">
            <h2 class="text-2xl font-bold">{{ __('Envianos tu mensaje') }}</h2>
            <div class="mt-6">
                <x-form-status />
                <form method="POST" action="{{ lroute('contact.send') }}#formulario" class="grid gap-5 sm:grid-cols-2" data-once>
                    @csrf
                    @include('partials.honeypot')
                    <x-field name="name" :label="__('Nombre')" required autocomplete="name" />
                    <x-field name="email" label="Email" type="email" required autocomplete="email" />
                    <x-field name="phone" :label="__('Teléfono')" type="tel" autocomplete="tel" />
                    <x-field name="subject" :label="__('Asunto')" required :value="request('asunto')" />
                    <x-field name="body" :label="__('Mensaje')" type="textarea" required class="sm:col-span-2" />
                    <div class="sm:col-span-2">
                        <button type="submit" class="btn btn-primary w-full sm:w-auto" data-label="{{ __('Enviando…') }}">{{ __('Enviar mensaje') }} <x-icon name="arrow-right" class="size-4" /></button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
