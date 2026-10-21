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
        <x-field name="specs" label="Características" type="textarea" rows="8" :value="$product->specsText()" class="sm:col-span-2"
            hint="Una por línea, con el formato Nombre: valor. Ej.: Conexión: USB. Se muestran en la ficha y se envían a Google." />
        <div class="sm:col-span-2">
            <label class="label" for="f-photo">Foto</label>
            <div class="flex items-center gap-4">
                <img src="{{ $product->imageUrl() }}" alt="" class="size-20 rounded-xl border border-zinc-200 bg-white object-contain p-1 dark:border-ink-700">
                <input id="f-photo" type="file" name="photo" accept="image/*" class="text-sm">
            </div>
            <p class="mt-1 text-xs text-zinc-500">JPG, PNG o WebP, hasta 4 MB.</p>
            @error('photo')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="sm:col-span-2">
            <label class="label" for="f-photos">Más fotos (otras posiciones)</label>
            @if ($product->images)
                <div class="mb-3 flex flex-wrap gap-3">
                    @foreach ($product->images as $path)
                        <label class="flex flex-col items-center gap-1 text-xs text-zinc-500">
                            <img src="{{ asset($path) }}" alt="" class="size-20 rounded-xl border border-zinc-200 bg-white object-contain p-1 dark:border-ink-700">
                            <span><input type="checkbox" name="remove_images[]" value="{{ $path }}"> Quitar</span>
                        </label>
                    @endforeach
                </div>
            @endif
            <input id="f-photos" type="file" name="photos[]" accept="image/*" multiple class="text-sm">
            <p class="mt-1 text-xs text-zinc-500">Podés elegir varias a la vez; se agregan después de la principal y se ven como miniaturas en la ficha.</p>
            @error('photos.*')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="flex flex-wrap gap-6 sm:col-span-2">
            <x-admin.checkbox name="featured" label="En oferta (aparece en el inicio)" :checked="$product->featured" />
            <x-admin.checkbox name="active" label="Visible en la tienda" :checked="$product->active" />
        </div>
    </x-admin.form>
@endsection
