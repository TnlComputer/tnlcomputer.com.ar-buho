@extends('layouts.admin')

@section('title', 'Zonas de envío')
@section('actions')
    <a href="{{ route('admin.zones.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Nueva zona</a>
@endsection

@section('content')
    <p class="mb-6 max-w-3xl text-sm text-zinc-500">El costo se elige por código postal: gana la primera zona (por orden) cuyo rango incluya el CP. La zona sin rangos cubre el resto del país. Las zonas de retiro se ofrecen siempre, sin cargo.</p>
    <div class="card overflow-x-auto">
        <table class="w-full min-w-[640px] text-sm">
            <thead class="border-b border-zinc-100 text-left text-xs text-zinc-500 uppercase dark:border-ink-700"><tr><th class="px-4 py-3">#</th><th>Zona</th><th>Códigos postales</th><th class="text-right">Costo</th><th class="text-right">Gratis desde</th><th></th></tr></thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-ink-700">
                @foreach ($zones as $zone)
                    <tr @class(['opacity-50' => ! $zone->active])>
                        <td class="px-4 py-2.5 text-zinc-500">{{ $zone->sort }}</td>
                        <td class="font-medium">{{ $zone->name }} @if ($zone->is_pickup)<span class="chip ml-1">retiro</span>@endif<span class="block text-xs font-normal text-zinc-500">{{ $zone->eta }}</span></td>
                        <td class="text-xs text-zinc-500">{{ $zone->is_pickup ? '—' : ($zone->postal_ranges ? str_replace("\n", ', ', $zone->postal_ranges) : 'resto del país') }}</td>
                        <td class="text-right font-semibold">{{ $zone->is_pickup ? 'Gratis' : money($zone->price) }}</td>
                        <td class="text-right">{{ $zone->free_from ? money($zone->free_from) : '—' }}</td>
                        <td class="px-4"><x-admin.row-actions :edit="route('admin.zones.edit', $zone)" :destroy="route('admin.zones.destroy', $zone)" /></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
