@extends('layouts.admin')

@section('title', 'Importar lista de precios')

@section('content')
    <form method="POST" action="{{ route('admin.products.import.store') }}" enctype="multipart/form-data" class="card max-w-3xl p-6 sm:p-8" data-once>
        @csrf
        <div class="space-y-5 text-sm">
            <p class="text-zinc-600 dark:text-zinc-400">Subí un CSV (Excel → Guardar como → CSV). Tiene que tener encabezado con al menos <strong>codigo</strong>, <strong>nombre</strong> y <strong>precio</strong>; opcionales: <strong>rubro</strong>, <strong>stock</strong>, <strong>imagen</strong>, <strong>descripcion</strong>. Si el código ya existe se actualiza el precio y los datos; si no, se crea.</p>
            <div>
                <label class="label" for="f-file">Archivo CSV <span class="text-brand-600">*</span></label>
                <input id="f-file" type="file" name="file" accept=".csv,text/csv" required class="text-sm">
                @error('file')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <x-field name="adjust" label="Ajuste de precio (%)" type="number" step="0.1" :value="config('tnl.markup')" hint="Recargo sobre el precio de la lista (por defecto el configurado en TNL_MARKUP). 0 = usar el precio tal cual." />
            <x-admin.checkbox name="deactivate" label="Ocultar los productos que no estén en este archivo" />
        </div>
        <div class="mt-8 flex gap-3 border-t border-zinc-100 pt-6 dark:border-ink-700">
            <button class="btn btn-primary" data-label="Importando…">Importar</button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
@endsection
