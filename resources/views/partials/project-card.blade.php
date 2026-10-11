{{-- Todas las tarjetas miden lo mismo: imagen de alto fijo (o marcador), texto recortado y enlaces abajo. --}}
<article class="group card flex h-full flex-col overflow-hidden">
    <div class="aspect-[4/3] shrink-0 overflow-hidden border-b border-zinc-100 bg-zinc-100 dark:border-ink-700 dark:bg-ink-800">
        @if ($project->imageUrl())
            <img src="{{ $project->imageUrl() }}" alt="{{ __('Captura de :title', ['title' => $project->tr('title')]) }}" loading="lazy" width="640" height="480"
                 class="size-full object-cover object-top transition duration-500 group-hover:scale-105">
        @else
            <div class="grid size-full place-items-center bg-gradient-to-br from-brand-50 to-sky-50 dark:from-brand-900/30 dark:to-ink-800">
                <div class="text-center">
                    <x-icon name="code" class="mx-auto size-10 text-brand-400" />
                    <p class="mt-2 px-6 font-display text-lg font-semibold text-zinc-700 dark:text-zinc-300">{{ $project->tr('title') }}</p>
                </div>
            </div>
        @endif
    </div>
    <div class="flex flex-1 flex-col p-5">
        <div class="flex items-center gap-2 text-xs text-zinc-500">
            @if ($project->kind)<span class="font-semibold text-brand-600 uppercase dark:text-brand-400">{{ $project->tr('kind') }}</span>@endif
            @if ($project->year)<span>· {{ $project->year }}</span>@endif
        </div>
        <h3 class="mt-2 line-clamp-2 min-h-[3.5rem] text-lg font-semibold">{{ $project->tr('title') }}</h3>
        <p class="mt-2 line-clamp-4 min-h-[5rem] text-sm text-zinc-600 dark:text-zinc-400" title="{{ $project->tr('description') }}">{{ $project->tr('description') }}</p>
        <div class="mt-4 flex max-h-[3.75rem] flex-wrap gap-1.5 overflow-hidden">
            @foreach ($project->tags ?? [] as $tag)<span class="chip">{{ $tag }}</span>@endforeach
        </div>
        <div class="mt-auto flex min-h-[2.75rem] items-end gap-4 border-t border-zinc-100 pt-4 text-sm font-semibold dark:border-ink-700">
            @if ($project->demo_url)
                <a href="{{ $project->demo_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-brand-600 hover:underline dark:text-brand-400"><x-icon name="external" class="size-4" /> {{ __('Ver sitio') }}</a>
            @endif
            @if ($project->code_url)
                <a href="{{ $project->code_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-zinc-600 hover:text-zinc-950 dark:text-zinc-400 dark:hover:text-white"><x-icon name="github" class="size-4" /> {{ __('Código') }}</a>
            @endif
            @if (! $project->demo_url && ! $project->code_url)
                <span class="text-zinc-400">{{ __('Proyecto privado') }}</span>
            @endif
        </div>
    </div>
</article>
