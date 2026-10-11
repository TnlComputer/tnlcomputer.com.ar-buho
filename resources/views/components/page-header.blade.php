@props(['eyebrow' => null, 'title', 'image' => null])
<section class="relative overflow-hidden border-b border-zinc-200 dark:border-ink-700">
    <div class="absolute inset-0 -z-10 bg-[radial-gradient(60%_80%_at_80%_0%,var(--color-brand-100),transparent)] dark:bg-[radial-gradient(60%_80%_at_80%_0%,color-mix(in_oklab,var(--color-brand-900)_60%,transparent),transparent)]"></div>
    <div class="container-x grid items-center gap-10 py-14 md:py-20 {{ $image ? 'lg:grid-cols-2' : '' }}">
        <div>
            @if ($eyebrow)
                <p class="eyebrow">{{ $eyebrow }}</p>
            @endif
            <h1 class="mt-3 text-4xl font-bold sm:text-5xl">{{ $title }}</h1>
            @if ($slot->isNotEmpty())
                <div class="mt-5 max-w-2xl text-lg text-zinc-600 dark:text-zinc-400">{{ $slot }}</div>
            @endif
            {{ $actions ?? '' }}
        </div>
        @if ($image)
            <img src="{{ asset($image) }}" alt="" class="hidden aspect-[4/3] w-full rounded-3xl object-cover shadow-2xl shadow-zinc-900/10 lg:block" width="640" height="480">
        @endif
    </div>
</section>
