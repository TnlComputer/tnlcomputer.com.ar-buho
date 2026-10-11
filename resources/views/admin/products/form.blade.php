@extends('layouts.admin')

@section('title', $product->exists ? 'Editar producto' : 'Nuevo producto')

@section('content')
    <x-admin.form :action="$product->exists ? route('admin.products.update', $product) : route('admin.products.store')" :model="$product" :back="route('admin.products.index')" files>
        <x-field name="code" label="Código" required :value="$product->code" />
        <x-field name="category_id" label="Rubro" type="select">
            <option value="">Sin rubro</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id) == $cat->id)>{{ $cat->name }}</option>
            @endforeach
        </x-field>
        <x-field name="name" label="Nombre / detalle" required :value="$product->name" class="sm:col-span-2" />
        <x-field name="price" label="Precio" type="number" step="0.01" min="0" required :value="$product->price" />
        <x-field name="stock" label="Stock" :value="$product->stock" hint="Texto libre: DISPONIBLE, CONSULTAR, SIN STOCK (no se muestra)…" />
        <x-field name="description" label="Descripción" type="textarea" :value="$product->description" class="sm:col-span-2" />
        <div class="sm:col-span-2">
            <label class="label" for="f-photo">Foto</label>
            <div class="flex items-center gap-4">
                <img src="{{ $product->imageUrl() }}" alt="" class="size-20 rounded-xl border border-zinc-200 bg-white object-contain p-1 dark:border-ink-700">
                <input id="f-photo" type="file" name="photo" accept="image/*" class="text-sm">
            </div>
            <p class="mt-1 text-xs text-zinc-500">JPG, PNG o WebP, hasta 4 MB.</p>
            @error('photo')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="flex flex-wrap gap-6 sm:col-span-2">
            <x-admin.checkbox name="featured" label="En oferta (aparece en el inicio)" :checked="$product->featured" />
            <x-admin.checkbox name="active" label="Visible en la tienda" :checked="$product->active" />
        </div>
    </x-admin.form>
@endsection
