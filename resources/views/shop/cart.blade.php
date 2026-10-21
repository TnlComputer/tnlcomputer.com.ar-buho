@extends('layouts.site')

@section('title', __('Carrito'))

@if ($lines->isNotEmpty())
    @push('analytics')
        <script nonce="{{ Vite::cspNonce() }}">gtag('event', 'view_cart', @json(['currency' => 'ARS', 'value' => $total, 'items' => $lines->map(fn ($l) => $l->product->gaItem($l->qty))->values()]));</script>
    @endpush
@endif

@section('content')
    <div class="container-x py-10">
        @include('shop.partials.steps', ['step' => 1])
        <h1 class="mt-8 text-3xl font-bold sm:text-4xl">{{ __('Tu carrito') }}</h1>

        @if ($lines->isEmpty())
            <div class="card mt-8 grid place-items-center px-6 py-20 text-center">
                <x-icon name="cart" class="size-10 text-zinc-300" />
                <p class="mt-4 text-lg font-semibold text-zinc-900 dark:text-white">{{ __('El carrito está vacío') }}</p>
                <a href="{{ lroute('shop.index') }}" class="btn btn-primary mt-6">{{ __('Ir a la tienda') }}</a>
            </div>
        @else
            <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_360px]">
                <ul class="card divide-y divide-zinc-100 dark:divide-ink-700">
                    @foreach ($lines as $line)
                        <li class="flex gap-4 p-4 sm:p-5">
                            <a href="{{ lroute('shop.show', $line->product) }}" class="size-20 shrink-0 rounded-xl bg-white p-1.5 sm:size-24">
                                <img src="{{ $line->product->imageUrl() }}" alt="" class="size-full object-contain" loading="lazy">
                            </a>
                            <div class="flex min-w-0 flex-1 flex-col">
                                <a href="{{ lroute('shop.show', $line->product) }}" class="line-clamp-2 font-semibold text-zinc-900 hover:text-brand-700 dark:text-white">{{ $line->product->name }}</a>
                                <p class="text-xs text-zinc-500">{{ __('Cód.') }} {{ $line->product->code }} · {{ money($line->product->price) }} {{ __('c/u') }}</p>
                                <div class="mt-auto flex items-center justify-between gap-3 pt-3">
                                    <form method="POST" action="{{ lroute('cart.update', $line->product) }}" class="flex items-center gap-2">
                                        @csrf @method('PATCH')
                                        <label class="sr-only" for="qty-{{ $line->product->id }}">{{ __('Cantidad') }}</label>
                                        <select id="qty-{{ $line->product->id }}" name="qty" class="input !w-20 !py-1.5" data-autosubmit>
                                            @foreach (range(1, max(10, $line->qty)) as $n)
                                                <option @selected($n === $line->qty)>{{ $n }}</option>
                                            @endforeach
                                        </select>
                                        <noscript><button class="text-sm underline">{{ __('Actualizar') }}</button></noscript>
                                    </form>
                                    <form method="POST" action="{{ lroute('cart.update', $line->product) }}">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="qty" value="0">
                                        <button class="text-sm text-zinc-500 hover:text-red-600" aria-label="{{ __('Quitar :product', ['product' => $line->product->name]) }}"><x-icon name="trash" class="size-4.5" /></button>
                                    </form>
                                    <p class="ml-auto font-display text-lg font-bold">{{ money($line->subtotal) }}</p>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <aside class="card h-fit p-6 lg:sticky lg:top-24">
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-zinc-600 dark:text-zinc-400">{{ __('Subtotal') }}</dt><dd class="font-semibold">{{ money($total) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-zinc-600 dark:text-zinc-400">{{ __('Envío') }}</dt><dd class="text-zinc-500">{{ __('Se calcula en el siguiente paso') }}</dd></div>
                    </dl>
                    <a href="{{ lroute('checkout') }}" class="btn btn-primary mt-6 w-full">{{ __('Continuar compra') }} <x-icon name="arrow-right" class="size-4" /></a>
                    @include('shop.partials.payment-badges')
                </aside>
            </div>
        @endif
    </div>
@endsection
