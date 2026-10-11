{{-- Rubros agrupados por familia. La familia del rubro actual queda abierta. --}}
<nav aria-label="{{ __('Rubros') }}">
    <a href="{{ lroute('shop.index', request()->only('q', 'orden')) }}"
       @class([
           'mb-2 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition',
           'bg-brand-600 text-white' => ! $category,
           'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-ink-800' => $category,
       ])>
        <x-icon name="box" class="size-4.5" /> {{ __('Todos los productos') }}
    </a>

    <div class="space-y-1">
        @foreach ($groups as $group)
            @php $open = $category && $group['items']->contains(fn ($c) => $c->is($category)); @endphp
            <details class="group/cat rounded-xl" @if ($open) open @endif>
                <summary class="flex cursor-pointer list-none items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-zinc-800 transition select-none hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-ink-800 [&::-webkit-details-marker]:hidden">
                    <span class="grid size-8 place-items-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-900/30 dark:text-brand-300"><x-icon :name="$group['icon']" class="size-4" /></span>
                    <span class="flex-1">{{ __($group['name']) }}</span>
                    <span class="text-xs font-normal text-zinc-400">{{ $group['total'] }}</span>
                    <x-icon name="arrow-right" class="size-3.5 text-zinc-400 transition group-open/cat:rotate-90" />
                </summary>
                <ul class="mt-1 mb-2 ml-7 space-y-0.5 border-l border-zinc-200 pl-3 dark:border-ink-700">
                    @foreach ($group['items'] as $cat)
                        <li>
                            <a href="{{ lroute('shop.category', [$cat] + request()->only('q', 'orden')) }}"
                               @class([
                                   'flex items-center justify-between gap-2 rounded-lg px-2.5 py-1.5 text-sm transition',
                                   'bg-brand-50 font-semibold text-brand-700 dark:bg-brand-900/30 dark:text-brand-300' => $category?->is($cat),
                                   'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950 dark:text-zinc-400 dark:hover:bg-ink-800 dark:hover:text-white' => ! $category?->is($cat),
                               ])
                               @if ($category?->is($cat)) aria-current="page" @endif>
                                <span class="truncate">{{ $cat->tr('name') }}</span>
                                <span class="text-xs text-zinc-400">{{ $cat->products_count }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </details>
        @endforeach
    </div>
</nav>
