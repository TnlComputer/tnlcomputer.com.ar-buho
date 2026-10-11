@props(['name', 'label', 'checked' => false])
<label class="flex items-center gap-3 text-sm font-medium">
    <input type="hidden" name="{{ $name }}" value="0">
    <input type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $checked)) class="size-4.5 rounded border-zinc-300 accent-brand-600">
    {{ $label }}
</label>
