@extends('layouts.admin')

@section('title', 'Planes de hosting')
@section('actions')
    <a href="{{ route('admin.plans.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Nuevo plan</a>
@endsection

@section('content')
    <div class="card overflow-x-auto">
        <table class="w-full min-w-[640px] text-sm">
            <thead class="border-b border-zinc-100 text-left text-xs text-zinc-500 uppercase dark:border-ink-700"><tr><th class="px-4 py-3">Plan</th><th>Panel</th><th>Disco / Transf.</th><th class="text-right">Mensual</th><th class="text-right">Anual</th><th></th></tr></thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-ink-700">
                @foreach ($plans as $plan)
                    <tr @class(['opacity-50' => ! $plan->active])>
                        <td class="px-4 py-2.5 font-medium">{{ $plan->name }} @if ($plan->highlighted)<span class="chip ml-1">destacado</span>@endif</td>
                        <td>{{ $plan->panelName() }}</td>
                        <td class="text-zinc-500">{{ $plan->disk }} / {{ $plan->transfer }}</td>
                        <td class="text-right font-semibold">{{ money($plan->price_monthly) }}</td>
                        <td class="text-right">{{ $plan->price_annual ? money($plan->price_annual) : '—' }}</td>
                        <td class="px-4"><x-admin.row-actions :edit="route('admin.plans.edit', $plan)" :destroy="route('admin.plans.destroy', $plan)" /></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
