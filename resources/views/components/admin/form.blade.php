@props(['action', 'model', 'back', 'files' => false])
<form method="POST" action="{{ $action }}" @if ($files) enctype="multipart/form-data" @endif class="card max-w-3xl p-6 sm:p-8" data-once>
    @csrf
    @if ($model->exists) @method('PUT') @endif

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-900 dark:border-red-900 dark:bg-red-950/50 dark:text-red-200" role="alert">Revisá los campos marcados.</div>
    @endif

    <div class="grid gap-5 sm:grid-cols-2">
        {{ $slot }}
    </div>

    <div class="mt-8 flex flex-wrap gap-3 border-t border-zinc-100 pt-6 dark:border-ink-700">
        <button type="submit" class="btn btn-primary" data-label="Guardando…">Guardar</button>
        <a href="{{ $back }}" class="btn btn-secondary">Cancelar</a>
    </div>
</form>
