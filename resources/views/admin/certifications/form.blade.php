@extends('layouts.admin')

@section('title', $certification->exists ? 'Editar certificación' : 'Nueva certificación')

@section('content')
    <x-admin.form :action="$certification->exists ? route('admin.certifications.update', $certification) : route('admin.certifications.store')" :model="$certification" :back="route('admin.certifications.index')" files>
        <x-field name="title" label="Título" required :value="$certification->title" class="sm:col-span-2" />
        <x-field name="issuer" label="Emisor" required :value="$certification->issuer" list="issuers" />
        <datalist id="issuers">
            @foreach ($issuers as $issuer)<option value="{{ $issuer }}">@endforeach
        </datalist>
        <x-field name="issued_at" label="Fecha" type="date" :value="$certification->issued_at?->format('Y-m-d')" />
        <div class="sm:col-span-2">
            <label class="label" for="f-document">Certificado (PDF o imagen)</label>
            <input id="f-document" type="file" name="document" accept=".pdf,image/*" class="text-sm">
            @if ($certification->file)<p class="mt-1 text-xs text-zinc-500">Actual: <a href="{{ $certification->fileUrl() }}" target="_blank" class="text-brand-600 underline">{{ basename($certification->file) }}</a></p>@endif
            @error('document')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="sm:col-span-2"><x-admin.checkbox name="featured" label="Destacada en el portfolio" :checked="$certification->featured" /></div>
    </x-admin.form>
@endsection
