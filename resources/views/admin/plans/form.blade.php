@extends('layouts.admin')

@section('title', $plan->exists ? 'Editar plan' : 'Nuevo plan')

@section('content')
    <x-admin.form :action="$plan->exists ? route('admin.plans.update', $plan) : route('admin.plans.store')" :model="$plan" :back="route('admin.plans.index')">
        <x-field name="name" label="Nombre" required :value="$plan->name" />
        <x-field name="panel" label="Panel" type="select" required>
            @foreach (\App\Models\HostingPlan::PANELS as $key => $label)
                <option value="{{ $key }}" @selected(old('panel', $plan->panel) === $key)>{{ $label }}</option>
            @endforeach
        </x-field>
        <x-field name="disk" label="Espacio" required :value="$plan->disk" placeholder="10 GB SSD" />
        <x-field name="transfer" label="Transferencia" required :value="$plan->transfer" placeholder="10 GB" />
        <x-field name="domains" label="Dominios" type="number" min="1" required :value="$plan->domains" />
        <x-field name="sort" label="Orden" type="number" min="0" :value="$plan->sort" />
        <x-field name="price_monthly" label="Precio mensual" type="number" step="0.01" min="0" required :value="$plan->price_monthly" />
        <x-field name="price_semester" label="Mensual pagando semestral" type="number" step="0.01" min="0" :value="$plan->price_semester" />
        <x-field name="price_annual" label="Mensual pagando anual" type="number" step="0.01" min="0" :value="$plan->price_annual" />
        <x-field name="price_biannual" label="Mensual pagando bianual" type="number" step="0.01" min="0" :value="$plan->price_biannual" />
        <div class="flex flex-wrap gap-6 sm:col-span-2">
            <x-admin.checkbox name="highlighted" label="Destacado (“Más elegido”)" :checked="$plan->highlighted" />
            <x-admin.checkbox name="active" label="Visible" :checked="$plan->active" />
        </div>
    </x-admin.form>
@endsection
