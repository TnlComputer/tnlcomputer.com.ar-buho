@props(['product'])
<article class="group card relative flex flex-col overflow-hidden transition hover:-translate-y-0.5 hover:shadow-xl hover:shadow-zinc-900/5 dark:hover:shadow-black/30">
    @if ($product->featured)
        <span class="absolute top-3 left-3 z-10 rounded-full bg-brand-600 px-2.5 py-1 text-[11px] font-bold tracking-wide text-white uppercase">{{ __('Oferta') }}</span>
    @endif
    <a href="{{ lroute('shop.show', $product) }}" class="block aspect-[4/3] overflow-hidden bg-white p-4">
        <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" loading="lazy" width="320" height="240"
             class="size-full object-contain transition duration-300 group-hover:scale-105">
    </a>
    <div class="flex flex-1 flex-col border-t border-zinc-100 p-4 dark:border-ink-700">
        @if ($product->category)
            <p class="text-xs font-medium text-zinc-500 uppercase">{{ $product->category->tr('name') }}</p>
        @endif
        <h3 class="mt-1 line-clamp-2 font-sans text-sm font-semibold text-zinc-900 dark:text-zinc-100">
            <a href="{{ lroute('shop.show', $product) }}" class="after:absolute after:inset-0">{{ $product->name }}</a>
        </h3>
        <div class="mt-auto flex items-end justify-between gap-2 pt-4">
            <div>
                <p class="font-display text-xl font-bold text-zinc-950 dark:text-white">{{ money($product->price) }}</p>
                @if ($product->stock)
                    <p class="text-xs text-zinc-500">{{ $product->stockLabel() }}</p>
                @endif
            </div>
            <form method="POST" action="{{ lroute('cart.add', $product) }}" class="relative z-10">
                @csrf
                <button class="grid size-10 place-items-center rounded-xl bg-brand-600 text-white transition hover:bg-brand-700" aria-label="{{ __('Agregar :product al carrito', ['product' => $product->name]) }}">
                    <x-icon name="cart" class="size-4.5" />
                </button>
            </form>
        </div>
    </div>
</article>
