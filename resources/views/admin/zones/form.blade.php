@extends('layouts.admin')

@section('title', $zone->exists ? 'Editar zona' : 'Nueva zona')

@section('content')
    <x-admin.form :action="$zone->exists ? route('admin.zones.update', $zone) : route('admin.zones.store')" :model="$zone" :back="route('admin.zones.index')">
        <x-field name="name" label="Nombre" required :value="$zone->name" />
        <x-field name="name_en" label="Nombre en inglés" :value="$zone->name_en" />
        <x-field name="eta" label="Demora / aclaración" :value="$zone->eta" placeholder="24 a 48 h hábiles" />
        <x-field name="eta_en" label="Demora en inglés" :value="$zone->eta_en" placeholder="1–2 business days" />
        <x-field name="price" label="Costo de envío" type="number" step="0.01" min="0" required :value="$zone->price" />
        <x-field name="free_from" label="Envío gratis desde (subtotal)" type="number" step="0.01" min="0" :value="$zone->free_from" hint="Vacío = nunca gratis." />
        <x-field name="postal_ranges" label="Rangos de código postal" type="textarea" rows="4" :value="$zone->postal_ranges" class="sm:col-span-2"
                 hint="Uno por línea, ej: 1000-1499. Vacío = resto del país." />
        <x-field name="sort" label="Orden" type="number" min="0" :value="$zone->sort" />
        <div class="flex flex-wrap items-end gap-6">
            <x-admin.checkbox name="is_pickup" label="Es retiro en el local (sin cargo)" :checked="$zone->is_pickup" />
            <x-admin.checkbox name="active" label="Activa" :checked="$zone->active" />
        </div>
    </x-admin.form>
@endsection
