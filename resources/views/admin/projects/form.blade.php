@extends('layouts.admin')

@section('title', $project->exists ? 'Editar proyecto' : 'Nuevo proyecto')

@section('content')
    <x-admin.form :action="$project->exists ? route('admin.projects.update', $project) : route('admin.projects.store')" :model="$project" :back="route('admin.projects.index')" files>
        <x-field name="title" label="Título" required :value="$project->title" class="sm:col-span-2" />
        <x-field name="title_en" label="Título en inglés" :value="$project->title_en" class="sm:col-span-2" />
        <x-field name="kind" label="Tipo" :value="$project->kind" placeholder="Sitio web, API, Sistema…" />
        <x-field name="kind_en" label="Tipo en inglés" :value="$project->kind_en" placeholder="Website, API, Web system…" />
        <x-field name="year" label="Año" type="number" :value="$project->year" />
        <div class="hidden sm:block"></div>
        <x-field name="description" label="Descripción" type="textarea" rows="3" :value="$project->description" class="sm:col-span-2" />
        <x-field name="description_en" label="Descripción en inglés" type="textarea" rows="3" :value="$project->description_en" class="sm:col-span-2" />
        <x-field name="tags" label="Tecnologías" :value="implode(', ', $project->tags ?? [])" hint="Separadas por coma." class="sm:col-span-2" />
        <x-field name="demo_url" label="URL del sitio / demo" type="url" :value="$project->demo_url" />
        <x-field name="code_url" label="URL del código (GitHub)" type="url" :value="$project->code_url" />
        <x-field name="sort" label="Orden" type="number" min="0" :value="$project->sort" />
        <div>
            <label class="label" for="f-photo">Imagen</label>
            <input id="f-photo" type="file" name="photo" accept="image/*" class="text-sm">
            @if ($project->imageUrl())<img src="{{ $project->imageUrl() }}" alt="" class="mt-2 h-20 rounded-lg">@endif
            @error('photo')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="flex flex-wrap gap-6 sm:col-span-2">
            <x-admin.checkbox name="featured" label="Destacado (aparece primero y en el inicio)" :checked="$project->featured" />
            <x-admin.checkbox name="active" label="Visible" :checked="$project->active" />
        </div>
    </x-admin.form>
@endsection
