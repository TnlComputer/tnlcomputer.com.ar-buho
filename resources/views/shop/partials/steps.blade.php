@php $steps = [__('Carrito'), __('Datos y envío'), __('Pago'), __('Confirmación')]; @endphp
<ol class="flex items-center gap-2 text-xs font-semibold sm:text-sm" aria-label="{{ __('Pasos de la compra') }}">
    @foreach ($steps as $i => $label)
        <li class="flex items-center gap-2" @if ($i + 1 === $step) aria-current="step" @endif>
            <span @class([
                'grid size-6 place-items-center rounded-full text-[11px]',
                'bg-brand-600 text-white' => $i + 1 <= $step,
                'bg-zinc-200 text-zinc-500 dark:bg-ink-800' => $i + 1 > $step,
            ])>@if ($i + 1 < $step)<x-icon name="check" class="size-3.5" />@else{{ $i + 1 }}@endif</span>
            <span @class(['hidden sm:inline', 'text-zinc-900 dark:text-white' => $i + 1 === $step, 'text-zinc-500' => $i + 1 !== $step])>{{ $label }}</span>
        </li>
        @if (! $loop->last)<li class="h-px w-6 bg-zinc-300 sm:w-10 dark:bg-ink-700" aria-hidden="true"></li>@endif
    @endforeach
</ol>
