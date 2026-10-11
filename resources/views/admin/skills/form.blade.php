@extends('layouts.admin')

@section('title', $skill->exists ? 'Editar skill' : 'Nuevo skill')

@section('content')
    <x-admin.form :action="$skill->exists ? route('admin.skills.update', $skill) : route('admin.skills.store')" :model="$skill" :back="route('admin.skills.index')">
        <x-field name="name" label="Nombre" required :value="$skill->name" />
        <x-field name="group" label="Grupo" required :value="$skill->group" list="groups" />
        <datalist id="groups">
            @foreach ($groups as $group)<option value="{{ $group }}">@endforeach
        </datalist>
        <x-field name="sort" label="Orden dentro del grupo" type="number" min="0" :value="$skill->sort" />
    </x-admin.form>
@endsection
