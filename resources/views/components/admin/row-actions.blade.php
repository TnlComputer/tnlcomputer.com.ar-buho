@props(['edit', 'destroy', 'confirm' => '¿Eliminar este registro?'])
<div class="flex justify-end gap-1">
    <a href="{{ $edit }}" class="grid size-9 place-items-center rounded-lg text-zinc-500 hover:bg-zinc-100 hover:text-brand-600 dark:hover:bg-ink-800" aria-label="Editar"><x-icon name="pen" class="size-4" /></a>
    <form method="POST" action="{{ $destroy }}" data-confirm="{{ $confirm }}">
        @csrf @method('DELETE')
        <button class="grid size-9 place-items-center rounded-lg text-zinc-500 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/50" aria-label="Eliminar"><x-icon name="trash" class="size-4" /></button>
    </form>
</div>
