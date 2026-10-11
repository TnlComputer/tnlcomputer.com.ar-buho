@extends('layouts.admin')

@section('title', 'Rubros')
@section('actions')
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Nuevo rubro</a>
@endsection

@section('content')
    <div class="card overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="border-b border-zinc-100 text-left text-xs text-zinc-500 uppercase dark:border-ink-700"><tr><th class="px-4 py-3">Rubro</th><th>Dirección</th><th class="text-right">Productos</th><th></th></tr></thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-ink-700">
                @foreach ($categories as $cat)
                    <tr @class(['opacity-50' => ! $cat->active])>
                        <td class="px-4 py-2.5 font-medium">{{ $cat->name }}</td>
                        <td class="text-zinc-500">/tienda/rubro/{{ $cat->slug }}</td>
                        <td class="text-right"><a href="{{ route('admin.products.index', ['rubro' => $cat->id]) }}" class="hover:text-brand-600">{{ $cat->products_count }}</a></td>
                        <td class="px-4"><x-admin.row-actions :edit="route('admin.categories.edit', $cat)" :destroy="route('admin.categories.destroy', $cat)" confirm="¿Eliminar el rubro {{ $cat->name }}? Sus productos quedan sin rubro." /></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
