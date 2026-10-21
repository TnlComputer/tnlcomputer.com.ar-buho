{{-- Estrellas de 0 a 5 (admite medias: 4.5). --}}
@props(['value' => 0])
@php $value = max(0, min(5, (float) $value)); @endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-0.5 text-amber-500']) }} role="img" aria-label="{{ __(':value de 5 estrellas', ['value' => str_replace('.', ',', (string) $value)]) }}">
    @for ($i = 1; $i <= 5; $i++)
        @php $fill = $value >= $i ? 1 : ($value >= $i - 0.5 ? 0.5 : 0); @endphp
        <svg class="size-[1em]" viewBox="0 0 24 24" aria-hidden="true">
            <defs><linearGradient id="star-{{ $i }}-{{ $fill * 10 }}"><stop offset="{{ $fill * 100 }}%" stop-color="currentColor"/><stop offset="{{ $fill * 100 }}%" stop-color="currentColor" stop-opacity="0.2"/></linearGradient></defs>
            <path fill="url(#star-{{ $i }}-{{ $fill * 10 }})" d="M12 2.5l2.94 5.96 6.56.95-4.75 4.63 1.12 6.54L12 17.5l-5.87 3.08 1.12-6.54L2.5 9.41l6.56-.95z"/>
        </svg>
    @endfor
</span>
