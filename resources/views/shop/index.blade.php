@extends('layouts.site')

@section('title', $category ? $category->tr('name') : __('Tienda'))
@section('description', $category
    ? __(':category en TNL Computer: precios, stock y envío. Pagá con tarjeta o Mercado Pago.', ['category' => $category->tr('name')])
    : __('Hardware, notebooks, componentes de PC y periféricos. Precios actualizados, envío a todo el país y pago con Mercado Pago.'))

@section('content')
    <div class="border-b border-zinc-200 bg-zinc-50 dark:border-ink-700 dark:bg-ink-900/60">
        <div class="container-x py-10">
            <nav class="text-sm text-zinc-500" aria-label="{{ __('Ruta') }}">
                <a href="{{ lroute('home') }}" class="hover:text-brand-600">{{ __('Inicio') }}</a> /
                @if ($category)
                    <a href="{{ lroute('shop.index') }}" class="hover:text-brand-600">{{ __('Tienda') }}</a> / <span class="text-zinc-900 dark:text-zinc-200">{{ $category->tr('name') }}</span>
                @else
                    <span class="text-zinc-900 dark:text-zinc-200">{{ __('Tienda') }}</span>
                @endif
            </nav>
            <h1 class="mt-3 text-3xl font-bold sm:text-4xl">{{ $category?->tr('name') ?? __('Hardware y software') }}</h1>
            <p class="mt-2 text-zinc-600 dark:text-zinc-400">
                {{ trans_choice(':count producto|:count productos', $products->total()) }}{{ request('q') ? ' '.__('para ":q"', ['q' => request('q')]) : '' }}
            </p>

            <form method="GET" action="{{ $category ? lroute('shop.category', $category) : lroute('shop.index') }}" class="mt-6 flex flex-col gap-3 sm:flex-row" role="search">
                <div class="relative flex-1">
                    <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-zinc-400" />
                    <label for="q" class="sr-only">{{ __('Buscar productos') }}</label>
                    <input id="q" type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('Buscar por nombre o código…') }}" class="input !pl-10 !pr-10 [&::-webkit-search-cancel-button]:hidden">
                    @if (filled(request('q')))
                        <a href="{{ $category ? lroute('shop.category', [$category] + request()->only('orden')) : lroute('shop.index', request()->only('orden')) }}"
                           class="absolute top-1/2 right-2 grid size-7 -translate-y-1/2 place-items-center rounded-full text-zinc-400 hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-ink-800 dark:hover:text-white"
                           aria-label="{{ __('Borrar búsqueda') }}" title="{{ __('Borrar búsqueda') }}"><x-icon name="x" class="size-4" /></a>
                    @endif
                </div>
                <label for="orden" class="sr-only">{{ __('Ordenar') }}</label>
                <select id="orden" name="orden" class="input sm:!w-52" data-autosubmit>
                    @foreach (['nombre' => __('Nombre (A-Z)'), 'menor-precio' => __('Menor precio'), 'mayor-precio' => __('Mayor precio'), 'nuevos' => __('Más recientes')] as $value => $label)
                        <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary">{{ __('Buscar') }}</button>
            </form>
        </div>
    </div>

    <div class="container-x grid gap-8 py-10 lg:grid-cols-[260px_1fr]">
        <aside>
            <details class="card group lg:hidden">
                <summary class="flex cursor-pointer list-none items-center justify-between px-4 py-3 font-semibold">
                    {{ __('Rubro') }}: {{ $category?->tr('name') ?? __('Todos') }}
                    <x-icon name="arrow-right" class="size-4 rotate-90 transition group-open:-rotate-90" />
                </summary>
                <div class="max-h-80 overflow-y-auto border-t border-zinc-100 p-2 dark:border-ink-700">
                    @include('shop.partials.categories')
                </div>
            </details>
            <div class="sticky top-24 hidden lg:block">
                <h2 class="mb-3 px-3 font-sans text-xs font-semibold tracking-wide text-zinc-500 uppercase">{{ __('Rubros') }}</h2>
                <div class="max-h-[calc(100dvh-9rem)] overflow-y-auto pr-1">
                    @include('shop.partials.categories')
                </div>
            </div>
        </aside>

        <div>
            @if ($products->isEmpty())
                <div class="card grid place-items-center px-6 py-20 text-center">
                    <x-icon name="search" class="size-10 text-zinc-300" />
                    <p class="mt-4 text-lg font-semibold text-zinc-900 dark:text-white">{{ __('No encontramos productos') }}</p>
                    <p class="mt-1 text-sm text-zinc-500">{{ __('Probá con otra búsqueda o consultanos: conseguimos casi todo.') }}</p>
                    <a href="{{ whatsapp_url(__('Hola, estoy buscando: :q', ['q' => request('q')])) }}" target="_blank" rel="noopener" class="btn btn-whatsapp mt-6"><x-icon name="whatsapp" /> {{ __('Consultar por WhatsApp') }}</a>
                </div>
            @else
                <div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 min-[1800px]:grid-cols-6">
                    @foreach ($products as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
                <div class="mt-10">{{ $products->links() }}</div>
            @endif
        </div>
    </div>
@endsection
