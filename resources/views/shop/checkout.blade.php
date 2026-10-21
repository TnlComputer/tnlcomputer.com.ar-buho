@extends('layouts.site')

@section('title', __('Finalizar compra'))

@push('head')<meta name="robots" content="noindex">@endpush

@push('analytics')
    <script nonce="{{ Vite::cspNonce() }}">gtag('event', 'begin_checkout', @json(['currency' => 'ARS', 'value' => $subtotal, 'items' => $lines->map(fn ($l) => $l->product->gaItem($l->qty))->values()]));</script>
@endpush

@section('content')
    <div class="container-x py-10">
        @include('shop.partials.steps', ['step' => 2])
        <h1 class="mt-8 text-3xl font-bold sm:text-4xl">{{ __('Datos y envío') }}</h1>

        @unless ($paymentsEnabled)
            <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-200" role="alert">
                {{ __('El pago online está momentáneamente deshabilitado. Podés dejar tu pedido y te contactamos para coordinar el pago.') }}
            </div>
        @endunless

        <form method="POST" action="{{ lroute('checkout.store') }}" class="mt-8 grid gap-8 lg:grid-cols-[1fr_380px]" data-once data-checkout
              data-quote-url="{{ lroute('shipping.quote') }}" data-free-label="{{ __('Gratis') }}">
            @csrf
            @include('partials.honeypot')

            <div class="space-y-6">
                @if ($errors->any())
                    <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-900 dark:border-red-900 dark:bg-red-950/50 dark:text-red-200" role="alert">{{ $errors->first('form') ?: __('Revisá los campos marcados.') }}</div>
                @endif

                <fieldset class="card p-6">
                    <legend class="float-left mb-5 font-display text-lg font-semibold">{{ __('Tus datos') }}</legend>
                    <div class="clear-both grid gap-5 sm:grid-cols-2">
                        <x-field name="name" :label="__('Nombre y apellido')" required autocomplete="name" class="sm:col-span-2" />
                        <x-field name="email" label="Email" type="email" required autocomplete="email" :hint="__('Te enviamos la confirmación y el seguimiento.')" />
                        <x-field name="phone" :label="__('Teléfono / WhatsApp')" type="tel" required autocomplete="tel" />
                        <x-field name="dni" :label="__('DNI / CUIT (para la factura)')" inputmode="numeric" />
                    </div>
                </fieldset>

                <fieldset class="card p-6">
                    <legend class="float-left mb-5 font-display text-lg font-semibold">{{ __('Entrega') }}</legend>
                    <div class="clear-both space-y-3">
                        @php $delivery = old('delivery', $pickups->isNotEmpty() ? 'pickup' : 'shipping'); @endphp
                        @foreach ($pickups as $pickup)
                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-zinc-200 p-4 has-checked:border-brand-500 has-checked:bg-brand-50/50 dark:border-ink-700 dark:has-checked:bg-brand-900/20">
                                <input type="radio" name="delivery" value="pickup" class="mt-1 accent-brand-600" data-delivery @checked($delivery === 'pickup' && (int) old('pickup_zone', $pickups->first()->id) === $pickup->id)
                                       data-pickup-zone="{{ $pickup->id }}">
                                <span class="flex-1">
                                    <span class="block font-semibold text-zinc-900 dark:text-white">{{ $pickup->tr('name') }}</span>
                                    @if ($pickup->tr('eta'))<span class="text-sm text-zinc-500">{{ $pickup->tr('eta') }}</span>@endif
                                </span>
                                <span class="font-semibold text-emerald-600">{{ __('Gratis') }}</span>
                            </label>
                        @endforeach
                        <input type="hidden" name="pickup_zone" value="{{ old('pickup_zone', $pickups->first()?->id) }}">

                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-zinc-200 p-4 has-checked:border-brand-500 has-checked:bg-brand-50/50 dark:border-ink-700 dark:has-checked:bg-brand-900/20">
                            <input type="radio" name="delivery" value="shipping" class="mt-1 accent-brand-600" data-delivery @checked($delivery === 'shipping')>
                            <span class="flex-1">
                                <span class="block font-semibold text-zinc-900 dark:text-white">{{ __('Envío a domicilio') }}</span>
                                <span class="text-sm text-zinc-500">{{ __('El costo depende de tu código postal.') }}</span>
                            </span>
                        </label>
                    </div>

                    <div class="mt-5 grid gap-5 sm:grid-cols-2" data-shipping-fields @if ($delivery !== 'shipping') hidden @endif>
                        <div>
                            <x-field name="postal_code" :label="__('Código postal (opcional)')" autocomplete="postal-code" inputmode="numeric" maxlength="10" data-postal data-optional :hint="__('Si no lo sabés, lo calculamos con la dirección.')" />
                            <p class="mt-1.5 text-sm" data-quote-result aria-live="polite"></p>
                        </div>
                        <x-field name="province" :label="__('Provincia')" type="select" autocomplete="address-level1" data-addr>
                            <option value="">{{ __('Elegí una opción') }}</option>
                            @foreach (array_keys(\App\Support\PostalCodeResolver::PROVINCES) as $prov)
                                <option value="{{ $prov }}" @selected(old('province') === $prov)>{{ $prov }}</option>
                            @endforeach
                        </x-field>
                        <x-field name="address" :label="__('Calle, número, piso y depto.')" autocomplete="street-address" class="sm:col-span-2" data-addr />
                        <x-field name="city" :label="__('Localidad')" autocomplete="address-level2" data-addr />
                    </div>
                </fieldset>

                <fieldset class="card p-6">
                    <legend class="float-left mb-5 font-display text-lg font-semibold">{{ __('Comentarios') }}</legend>
                    <div class="clear-both">
                        <x-field name="notes" :label="__('¿Algo que debamos saber? (opcional)')" type="textarea" rows="3" :placeholder="__('Horario de entrega, factura A, etc.')" />
                    </div>
                </fieldset>
            </div>

            <aside class="card h-fit p-6 lg:sticky lg:top-24">
                <h2 class="font-sans text-base font-semibold">{{ __('Resumen') }}</h2>
                <ul class="mt-4 space-y-3 text-sm">
                    @foreach ($lines as $line)
                        <li class="flex gap-3">
                            <span class="text-zinc-500">{{ $line->qty }}×</span>
                            <span class="min-w-0 flex-1 truncate">{{ $line->product->name }}</span>
                            <span class="font-medium">{{ money($line->subtotal) }}</span>
                        </li>
                    @endforeach
                </ul>
                <dl class="mt-5 space-y-2 border-t border-zinc-100 pt-4 text-sm dark:border-ink-700">
                    <div class="flex justify-between"><dt class="text-zinc-600 dark:text-zinc-400">{{ __('Subtotal') }}</dt><dd>{{ money($subtotal) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-zinc-600 dark:text-zinc-400">{{ __('Envío') }}</dt><dd data-summary-shipping>{{ $delivery === 'pickup' ? __('Gratis') : '—' }}</dd></div>
                    <div class="flex items-baseline justify-between border-t border-zinc-100 pt-3 dark:border-ink-700">
                        <dt class="font-semibold">{{ __('Total') }}</dt>
                        <dd class="font-display text-2xl font-bold text-zinc-950 dark:text-white" data-summary-total>{{ money($subtotal) }}</dd>
                    </div>
                </dl>

                <label class="mt-5 flex items-start gap-2.5 text-xs text-zinc-600 dark:text-zinc-400">
                    <input type="checkbox" name="terms" value="1" required class="mt-0.5 accent-brand-600" @checked(old('terms'))>
                    <span>{{ __('Confirmo que los datos son correctos y acepto que TNL Computer me contacte para coordinar la entrega. Puedo arrepentirme de la compra dentro de los 10 días de recibido el producto.') }}</span>
                </label>
                @error('terms')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

                <button type="submit" class="btn btn-primary mt-5 w-full" data-label="{{ __('Redirigiendo a Mercado Pago…') }}">
                    <x-icon name="shield" class="size-4" /> {{ $paymentsEnabled ? __('Ir a pagar') : __('Enviar pedido') }}
                </button>
                @include('shop.partials.payment-badges')
            </aside>
        </form>
    </div>
@endsection
