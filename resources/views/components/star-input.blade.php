{{--
    Selector de 1 a 5 estrellas sin JavaScript. Los radios van del 5 al 1 y se muestran al revés (flex-row-reverse):
    así, la estrella elegida y las que quedan a su izquierda se pintan con "peer-checked" y con hover.
--}}
@props(['name', 'value' => null])
<fieldset class="flex w-fit flex-row-reverse justify-end gap-1 text-3xl text-zinc-300 dark:text-zinc-600 [&>label:hover]:text-amber-500 [&>label:hover~label]:text-amber-500">
    <legend class="sr-only">{{ __('Calificación') }}</legend>
    @for ($i = 5; $i >= 1; $i--)
        <input type="radio" id="{{ $name }}-{{ $i }}" name="{{ $name }}" value="{{ $i }}" class="peer sr-only" @checked((int) $value === $i)>
        <label for="{{ $name }}-{{ $i }}" class="cursor-pointer leading-none peer-checked:text-amber-500" title="{{ trans_choice(':count estrella|:count estrellas', $i) }}">
            <svg class="size-[1em]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.5l2.94 5.96 6.56.95-4.75 4.63 1.12 6.54L12 17.5l-5.87 3.08 1.12-6.54L2.5 9.41l6.56-.95z"/></svg>
            <span class="sr-only">{{ trans_choice(':count estrella|:count estrellas', $i) }}</span>
        </label>
    @endfor
</fieldset>
