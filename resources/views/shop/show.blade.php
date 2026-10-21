@extends('layouts.site')

@section('title', $product->name)
@section('description', \Illuminate\Support\Str::limit(($product->description ?: $product->name).' — '.money($product->price).' '.__('en TNL Computer.'), 155))
@section('og_image', $product->imageUrl())

@php
    $rating = $product->rating();
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product->name,
        'sku' => $product->code,
        'image' => $product->imageUrl(),
        'category' => $product->category?->tr('name'),
        'offers' => [
            '@type' => 'Offer',
            'priceCurrency' => 'ARS',
            'price' => (string) $product->price,
            'availability' => 'https://schema.org/InStock',
            'url' => url()->current(),
        ],
    ];
    if ($rating) {
        // Solo reseñas reales de compradores, aprobadas en el panel.
        $schema['aggregateRating'] = ['@type' => 'AggregateRating', 'ratingValue' => $rating['average'], 'reviewCount' => $rating['count'], 'bestRating' => 5, 'worstRating' => 1];
        $schema['review'] = $product->reviews->take(5)->map(fn ($r) => array_filter([
            '@type' => 'Review',
            'author' => ['@type' => 'Person', 'name' => $r->author],
            'datePublished' => $r->approved_at?->toDateString(),
            'reviewRating' => ['@type' => 'Rating', 'ratingValue' => $r->rating, 'bestRating' => 5, 'worstRating' => 1],
            'reviewBody' => $r->comment,
        ]))->values()->all();
    }
@endphp

@push('head')
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush

@push('analytics')
    <script nonce="{{ Vite::cspNonce() }}">gtag('event', 'view_item', @json(['currency' => 'ARS', 'value' => (float) $product->price, 'items' => [$product->gaItem()]]));</script>
@endpush

@section('content')
    <div class="container-x py-10">
        <nav class="text-sm text-zinc-500" aria-label="{{ __('Ruta') }}">
            <a href="{{ lroute('shop.index') }}" class="hover:text-brand-600">{{ __('Tienda') }}</a>
            @if ($product->category)
                / <a href="{{ lroute('shop.category', $product->category) }}" class="hover:text-brand-600">{{ $product->category->tr('name') }}</a>
            @endif
        </nav>

        <div class="mt-6 grid gap-10 lg:grid-cols-2">
            <div class="card relative grid aspect-square place-items-center overflow-hidden bg-white p-8 dark:bg-white">
                @if ($product->featured)
                    <span class="absolute top-4 left-4 rounded-full bg-brand-600 px-3 py-1 text-xs font-bold text-white uppercase">{{ __('Oferta') }}</span>
                @endif
                <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="max-h-full w-full object-contain" width="600" height="600">
            </div>

            <div>
                <p class="text-sm text-zinc-500">{{ __('Código') }}: {{ $product->code }}</p>
                <h1 class="mt-2 text-3xl font-bold sm:text-4xl">{{ $product->name }}</h1>
                @if ($rating)
                    <a href="#resenas" class="mt-3 inline-flex items-center gap-2 text-sm text-zinc-600 hover:text-zinc-950 dark:text-zinc-400 dark:hover:text-white">
                        <x-stars :value="$rating['average']" class="text-base" />
                        <span>{{ str_replace('.', ',', (string) $rating['average']) }} · {{ trans_choice(':count opinión|:count opiniones', $rating['count']) }}</span>
                    </a>
                @endif
                <p class="mt-6 font-display text-4xl font-bold text-zinc-950 dark:text-white">{{ money($product->price) }}</p>
                <p class="mt-1 text-sm text-zinc-500">{{ __('Precio final con IVA. Pagalo en cuotas con tarjeta de crédito.') }}</p>
                @if ($product->stock)
                    <p class="mt-3 inline-flex items-center gap-2 text-sm font-medium text-emerald-700 dark:text-emerald-400">
                        <span class="size-2 rounded-full bg-emerald-500"></span> {{ $product->stockLabel() }}
                    </p>
                @endif

                @if ($product->description)
                    <div class="mt-6 text-zinc-600 dark:text-zinc-400">{!! nl2br(e($product->description)) !!}</div>
                @endif

                @if ($product->specs)
                    <div class="mt-6">
                        <h2 class="font-sans text-base font-semibold">{{ __('Características') }}</h2>
                        <dl class="mt-3 divide-y divide-zinc-100 rounded-xl border border-zinc-200 text-sm dark:divide-ink-700 dark:border-ink-700">
                            @foreach ($product->specs as [$label, $value])
                                <div class="grid grid-cols-[minmax(0,2fr)_minmax(0,3fr)] gap-4 px-4 py-2.5">
                                    <dt class="text-zinc-500">{{ $label }}</dt>
                                    <dd class="font-medium text-zinc-900 dark:text-white">{{ $value }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @endif

                <form method="POST" action="{{ lroute('cart.add', $product) }}" class="mt-8 flex flex-wrap gap-3">
                    @csrf
                    <label for="qty" class="sr-only">{{ __('Cantidad') }}</label>
                    <input id="qty" type="number" name="qty" value="1" min="1" max="99" class="input !w-20 text-center">
                    <button class="btn btn-primary flex-1 sm:flex-none"><x-icon name="cart" class="size-4" /> {{ __('Agregar al carrito') }}</button>
                    <a href="{{ whatsapp_url(__('Hola, quería consultar por :product (código :code).', ['product' => $product->name, 'code' => $product->code])) }}" target="_blank" rel="noopener" class="btn btn-whatsapp flex-1 sm:flex-none">
                        <x-icon name="whatsapp" class="size-4" /> {{ __('Consultar') }}
                    </a>
                </form>

                <ul class="mt-8 space-y-3 border-t border-zinc-200 pt-6 text-sm text-zinc-600 dark:border-ink-700 dark:text-zinc-400">
                    <li class="flex gap-3"><x-icon name="shield" class="size-5 shrink-0 text-brand-600 dark:text-brand-400" /> {{ __('Pago seguro con Mercado Pago: crédito, débito, dinero en cuenta o dos tarjetas.') }}</li>
                    <li class="flex gap-3"><x-icon name="truck" class="size-5 shrink-0 text-brand-600 dark:text-brand-400" /> {{ __('Envío a todo el país según tu código postal, o retiro sin cargo.') }}</li>
                    <li class="flex gap-3"><x-icon name="tag" class="size-5 shrink-0 text-brand-600 dark:text-brand-400" /> {{ __('Te asesoramos sobre compatibilidad y garantía antes de comprar.') }}</li>
                </ul>
            </div>
        </div>

        @if ($rating)
            <section id="resenas" class="mt-16 scroll-mt-24">
                <div class="flex flex-wrap items-end gap-x-6 gap-y-2">
                    <h2 class="text-2xl font-bold">{{ __('Opiniones de compradores') }}</h2>
                    <p class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                        <x-stars :value="$rating['average']" class="text-lg" />
                        <strong class="text-zinc-950 dark:text-white">{{ str_replace('.', ',', (string) $rating['average']) }}</strong> {{ __('de 5') }} · {{ trans_choice(':count opinión|:count opiniones', $rating['count']) }}
                    </p>
                </div>
                <ul class="mt-6 grid gap-4 md:grid-cols-2">
                    @foreach ($product->reviews as $review)
                        <li class="card p-5">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <x-stars :value="$review->rating" />
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-700 dark:text-emerald-400"><x-icon name="check" class="size-3.5" /> {{ __('Compra verificada') }}</span>
                            </div>
                            @if ($review->comment)
                                <p class="mt-3 text-sm whitespace-pre-line text-zinc-700 dark:text-zinc-300">{{ $review->comment }}</p>
                            @endif
                            <p class="mt-3 text-xs text-zinc-500">{{ $review->author }} · {{ $review->approved_at?->translatedFormat('d M Y') }}</p>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if ($related->isNotEmpty())
            <section class="mt-20">
                <h2 class="text-2xl font-bold">{{ __('También te puede interesar') }}</h2>
                <div class="mt-6 grid grid-cols-2 gap-4 md:grid-cols-4">
                    @foreach ($related as $item)
                        <x-product-card :product="$item" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
