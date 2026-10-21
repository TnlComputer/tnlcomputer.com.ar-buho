@extends('layouts.admin')

@section('title', 'Reseñas')

@section('actions')
    @if (config('tnl.google_review'))
        <a href="{{ route('admin.reviews.poster') }}" target="_blank" class="btn btn-secondary"><x-icon name="star" class="size-4" /> Cartel con QR para el local</a>
    @endif
@endsection

@section('content')
    <p class="mb-4 max-w-3xl text-sm text-zinc-500">Las dejan los clientes desde la página de su pedido cuando figura como entregado. Se publican en la ficha del producto recién cuando las aprobás. Publicá también las negativas mientras sean respetuosas: le dan credibilidad al resto.</p>

    <div class="mb-4 flex flex-wrap gap-2">
        @foreach (\App\Models\Review::STATUSES as $key => $label)
            <a href="{{ route('admin.reviews.index', ['estado' => $key]) }}" @class(['chip !px-3.5 !py-1.5', '!bg-brand-600 !text-white' => $status === $key])>{{ $label }} ({{ $counts[$key] ?? 0 }})</a>
        @endforeach
    </div>

    <div class="space-y-4">
        @forelse ($reviews as $review)
            <div class="card flex flex-wrap items-start gap-4 p-5">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-3">
                        <x-stars :value="$review->rating" class="text-lg" />
                        <span class="text-sm font-medium">{{ $review->author }}</span>
                        <span class="text-xs text-zinc-500">{{ $review->created_at->format('d/m/Y H:i') }} · <a href="{{ route('admin.orders.show', $review->order_id) }}" class="text-brand-600 hover:underline">{{ $review->order?->number() }}</a></span>
                    </div>
                    <p class="mt-2 text-sm">
                        @if ($review->product)
                            <a href="{{ route('shop.show', $review->product) }}" target="_blank" class="font-medium hover:underline">{{ $review->product->name }}</a>
                        @endif
                    </p>
                    <p class="mt-2 text-sm whitespace-pre-line text-zinc-700 dark:text-zinc-300">{{ $review->comment ?: 'Sin comentario, solo estrellas.' }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if ($review->status !== 'approved')
                        <form method="POST" action="{{ route('admin.reviews.update', $review) }}">@csrf @method('PUT')<button name="status" value="approved" class="btn btn-primary !py-2">Publicar</button></form>
                    @endif
                    @if ($review->status !== 'rejected')
                        <form method="POST" action="{{ route('admin.reviews.update', $review) }}">@csrf @method('PUT')<button name="status" value="rejected" class="btn btn-secondary !py-2">{{ $review->status === 'approved' ? 'Despublicar' : 'Rechazar' }}</button></form>
                    @endif
                    <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" data-confirm="¿Eliminar esta reseña? No se puede deshacer.">@csrf @method('DELETE')<button class="btn !py-2 text-sm font-semibold text-red-600 hover:underline">Eliminar</button></form>
                </div>
            </div>
        @empty
            <div class="card px-4 py-10 text-center text-sm text-zinc-500">No hay reseñas {{ mb_strtolower(\App\Models\Review::STATUSES[$status]) }}s.</div>
        @endforelse
    </div>
    <div class="mt-6">{{ $reviews->links() }}</div>
@endsection
