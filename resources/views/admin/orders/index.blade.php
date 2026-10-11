@extends('layouts.admin')

@section('title', 'Pedidos')

@section('content')
    <div class="mb-4 flex flex-wrap gap-2">
        <a href="{{ route('admin.orders.index') }}" @class(['chip !px-3.5 !py-1.5', '!bg-brand-600 !text-white' => ! $status])>Todos</a>
        @foreach (\App\Models\Order::STATUSES as $key => $label)
            @if ($counts[$key] ?? 0)
                <a href="{{ route('admin.orders.index', ['estado' => $key]) }}" @class(['chip !px-3.5 !py-1.5', '!bg-brand-600 !text-white' => $status === $key])>{{ $label }} ({{ $counts[$key] }})</a>
            @endif
        @endforeach
    </div>
    <form method="GET" class="mb-4 flex gap-3">
        @if ($status)<input type="hidden" name="estado" value="{{ $status }}">@endif
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar por cliente, email o número" class="input flex-1" aria-label="Buscar">
        <button class="btn btn-secondary">Buscar</button>
    </form>

    <div class="card overflow-x-auto">
        <table class="w-full min-w-[720px] text-sm">
            <thead class="border-b border-zinc-100 text-left text-xs text-zinc-500 uppercase dark:border-ink-700">
                <tr><th class="px-4 py-3">Pedido</th><th>Fecha</th><th>Cliente</th><th>Entrega</th><th class="text-right">Total</th><th class="px-4">Estado</th></tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-ink-700">
                @forelse ($orders as $order)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-ink-800/60">
                        <td class="px-4 py-2.5"><a href="{{ route('admin.orders.show', $order) }}" class="font-semibold text-brand-600 hover:underline">{{ $order->number() }}</a></td>
                        <td class="text-zinc-500">{{ $order->created_at->format('d/m H:i') }}</td>
                        <td>{{ $order->customer_name }} <span class="block text-xs text-zinc-500">{{ $order->email }}</span></td>
                        <td class="text-zinc-500">{{ $order->shipping_zone_name }}</td>
                        <td class="text-right font-semibold">{{ money($order->total) }}</td>
                        <td class="px-4">@include('admin.orders.badge', ['order' => $order])</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-zinc-500">Todavía no hay pedidos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $orders->links() }}</div>
@endsection
