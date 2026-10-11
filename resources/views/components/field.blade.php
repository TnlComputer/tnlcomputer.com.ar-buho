@props(['name', 'label', 'type' => 'text', 'required' => false, 'value' => null, 'hint' => null])
<div {{ $attributes->only('class') }}>
    <label for="f-{{ $name }}" class="label">{{ $label }}@if ($required) <span class="text-brand-600">*</span>@endif</label>
    @if ($type === 'textarea')
        <textarea id="f-{{ $name }}" name="{{ $name }}" @required($required) {{ $attributes->except('class')->merge(['class' => 'input', 'rows' => 5]) }}>{{ old($name, $value) }}</textarea>
    @elseif ($type === 'select')
        <select id="f-{{ $name }}" name="{{ $name }}" @required($required) {{ $attributes->except('class')->merge(['class' => 'input']) }}>
            {{ $slot }}
        </select>
    @else
        <input id="f-{{ $name }}" type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $value) }}" @required($required) {{ $attributes->except('class')->merge(['class' => 'input']) }}>
    @endif
    @if ($hint)
        <p class="mt-1 text-xs text-zinc-500">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="mt-1 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
