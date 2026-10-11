@extends('layouts.admin')

@section('title', $category->exists ? 'Editar rubro' : 'Nuevo rubro')

@section('content')
    <x-admin.form :action="$category->exists ? route('admin.categories.update', $category) : route('admin.categories.store')" :model="$category" :back="route('admin.categories.index')">
        <x-field name="name" label="Nombre" required :value="$category->name" />
        <x-field name="name_en" label="Nombre en inglés" :value="$category->name_en" />
        <x-field name="slug" label="Dirección (slug)" :value="$category->slug" hint="Vacío = se genera del nombre." />
        <div class="sm:col-span-2"><x-admin.checkbox name="active" label="Visible en la tienda" :checked="$category->active" /></div>
    </x-admin.form>
@endsection
