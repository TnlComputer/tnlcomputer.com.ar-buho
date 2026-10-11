@extends('layouts.admin')

@section('title', 'Productos')
@section('actions')
    <div class="flex gap-2">
        <a href="{{ route('admin.products.import') }}" class="btn btn-secondary">Importar lista de precios</a>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Nuevo producto</a>
    </div>
@endsection

@section('content')
    @if (session('import_errors'))
        <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-200">
            <p class="font-semibold">Filas salteadas:</p>
            <ul class="mt-1 list-disc pl-5">@foreach (session('import_errors') as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif
    <form method="GET" class="mb-4 flex flex-wrap gap-3">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar por nombre o código" class="input !w-auto flex-1" aria-label="Buscar">
        <select name="rubro" class="input !w-auto" aria-label="Rubro" data-autosubmit>
            <option value="">Todos los rubros</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" @selected(request('rubro') == $cat->id)>{{ $cat->name }}</option>
            @endforeach
        </select>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="ofertas" value="1" @checked(request('ofertas')) class="accent-brand-600" data-autosubmit> Solo ofertas</label>
        <button class="btn btn-secondary">Filtrar</button>
    </form>

    <div class="card overflow-x-auto">
        <table class="w-full min-w-[720px] text-sm">
            <thead class="border-b border-zinc-100 text-left text-xs text-zinc-500 uppercase dark:border-ink-700">
                <tr><th class="px-4 py-3"></th><th class="py-3">Producto</th><th>Rubro</th><th class="text-right">Precio</th><th>Stock</th><th class="text-center">Oferta</th><th class="text-center">Activo</th><th></th></tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-ink-700">
                @forelse ($products as $product)
                    <tr @class(['opacity-50' => ! $product->active])>
                        <td class="px-4 py-2"><img src="{{ $product->imageUrl() }}" alt="" class="size-11 rounded-lg bg-white object-contain p-0.5" loading="lazy"></td>
                        <td class="py-2 pr-4"><p class="font-medium text-zinc-900 dark:text-white">{{ $product->name }}</p><p class="text-xs text-zinc-500">{{ $product->code }}</p></td>
                        <td class="pr-4 text-zinc-600 dark:text-zinc-400">{{ $product->category?->name ?? '—' }}</td>
                        <td class="pr-4 text-right font-semibold whitespace-nowrap">{{ money($product->price) }}</td>
                        <td class="pr-4 text-xs">{{ $product->stock }}</td>
                        @foreach (['featured', 'active'] as $field)
                            <td class="text-center">
                                <form method="POST" action="{{ route('admin.products.update', $product) }}">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="toggle" value="{{ $field }}">
                                    <button @class(['inline-grid size-7 place-items-center rounded-md', 'bg-brand-600 text-white' => $product->{$field}, 'border border-zinc-300 dark:border-ink-700' => ! $product->{$field}])
                                            aria-label="{{ $field === 'featured' ? 'Oferta' : 'Activo' }}: {{ $product->{$field} ? 'sí' : 'no' }}" aria-pressed="{{ $product->{$field} ? 'true' : 'false' }}">
                                        @if ($product->{$field})<x-icon name="check" class="size-4" />@endif
                                    </button>
                                </form>
                            </td>
                        @endforeach
                        <td class="px-4"><x-admin.row-actions :edit="route('admin.products.edit', $product)" :destroy="route('admin.products.destroy', $product)" confirm="¿Eliminar {{ $product->name }}?" /></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-10 text-center text-zinc-500">No hay productos. Importá una lista de precios o creá uno nuevo.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $products->links() }}</div>
@endsection
