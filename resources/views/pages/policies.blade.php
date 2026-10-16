@extends('layouts.site')

@section('title', __('Envíos, cambios y devoluciones'))
@section('description', __('Costos y plazos de envío, retiro sin cargo, derecho de arrepentimiento de 10 días, cambios, devoluciones y garantía en TNL Computer.'))

@php
    $orderMessage = __('Hola, quiero arrepentirme de mi compra. Pedido número: ');
@endphp

@section('content')
    <x-page-header :eyebrow="__('Tienda')" :title="__('Envíos, cambios y devoluciones')">
        {{ __('Todo lo que necesitás saber antes y después de tu compra.') }}
    </x-page-header>

    <div class="container-x max-w-3xl space-y-12 py-16 md:py-20">
        {{-- Envíos --}}
        <section id="envios">
            <h2 class="text-2xl font-bold">{{ __('Envíos y retiro') }}</h2>
            <p class="mt-4 text-zinc-600 dark:text-zinc-400">{{ __('Enviamos a todo el país. El costo se calcula en el checkout según tu código postal, y los plazos corren en días hábiles desde que se acredita el pago.') }}</p>
            <div class="card mt-6 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-zinc-100 text-xs text-zinc-500 uppercase dark:border-ink-700">
                        <tr><th class="px-4 py-3">{{ __('Zona') }}</th><th class="px-4 py-3">{{ __('Plazo') }}</th><th class="px-4 py-3 text-right">{{ __('Costo') }}</th></tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-ink-700">
                        @foreach ($zones as $zone)
                            <tr>
                                <td class="px-4 py-3 font-medium">{{ $zone->tr('name') }}</td>
                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ $zone->tr('eta') }}</td>
                                <td class="px-4 py-3 text-right">
                                    {{ $zone->is_pickup || (float) $zone->price <= 0 ? __('Gratis') : money($zone->price) }}
                                    @if (! $zone->is_pickup && $zone->free_from)
                                        <span class="block text-xs text-emerald-700 dark:text-emerald-400">{{ __('Gratis desde :amount', ['amount' => money($zone->free_from)]) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-4 text-sm text-zinc-600 dark:text-zinc-400">{{ __('Cuando el pedido sale, te pasamos el código de seguimiento, que también ves en la página de tu pedido.') }}</p>
        </section>

        {{-- Arrepentimiento --}}
        <section id="arrepentimiento">
            <h2 class="text-2xl font-bold">{{ __('Botón de arrepentimiento') }}</h2>
            <div class="prose-tnl mt-4 text-zinc-600 dark:text-zinc-400">
                <p>{{ __('Tenés 10 días corridos para arrepentirte de tu compra, contados desde que recibís el producto (o desde la compra, lo que ocurra último), sin dar explicaciones y sin costo (Ley 24.240 de Defensa del Consumidor, art. 34, y Resolución 424/2020).') }}</p>
                <p>{{ __('El producto tiene que estar sin uso, con sus accesorios, manuales y embalaje original. Nosotros nos hacemos cargo del costo de la devolución, y te reintegramos el total por el mismo medio con el que pagaste apenas recibimos el producto.') }}</p>
                <p>{{ __('Si todavía no pagaste, podés cancelar el pedido directamente desde su página.') }}</p>
            </div>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ whatsapp_url($orderMessage) }}" target="_blank" rel="noopener" class="btn btn-whatsapp"><x-icon name="whatsapp" class="size-4" /> {{ __('Arrepentirme por WhatsApp') }}</a>
                <a href="mailto:{{ config('tnl.email') }}?subject={{ rawurlencode(__('Arrepentimiento de compra')) }}" class="btn btn-secondary"><x-icon name="mail" class="size-4" /> {{ __('Arrepentirme por email') }}</a>
            </div>
            <p class="mt-3 text-sm text-zinc-500">{{ __('Indicá tu número de pedido (TNL-…). Dentro de las 24 horas te confirmamos el pedido de arrepentimiento con un código de seguimiento.') }}</p>
        </section>

        {{-- Cambios y garantía --}}
        <section id="garantia">
            <h2 class="text-2xl font-bold">{{ __('Cambios y garantía') }}</h2>
            <div class="prose-tnl mt-4 text-zinc-600 dark:text-zinc-400">
                <p>{{ __('Todos los productos son nuevos y tienen garantía por falla de fabricación: como mínimo 6 meses, o la del fabricante si es mayor. Si un producto falla, escribinos con el número de pedido: lo revisamos y, según el caso, lo reparamos, lo cambiamos por uno igual o te devolvemos el dinero.') }}</p>
                <p>{{ __('Dentro de los 10 días también podés cambiarlo por otro producto, siempre que esté sin uso y completo. Si el nuevo cuesta distinto, ajustamos la diferencia.') }}</p>
                <p>{{ __('La garantía no cubre daños por mal uso, golpes, humedad, picos de tensión ni equipos abiertos o modificados por terceros. Las licencias de software y los productos con sello de seguridad no tienen devolución una vez abiertos o activados.') }}</p>
            </div>
        </section>

        {{-- Contacto --}}
        <section class="card p-6">
            <h2 class="font-sans text-base font-semibold">{{ __('¿Dudas con tu compra?') }}</h2>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">{{ __('Escribinos por WhatsApp al :phone o a :email y te respondemos a la brevedad.', ['phone' => config('tnl.whatsapp_label'), 'email' => config('tnl.email')]) }}</p>
        </section>
    </div>
@endsection
