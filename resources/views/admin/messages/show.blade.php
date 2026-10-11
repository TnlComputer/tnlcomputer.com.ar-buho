@extends('layouts.admin')

@section('title', (\App\Models\Message::TYPES[$message->type] ?? 'Mensaje').' #'.$message->id)

@section('actions')
    <div class="flex gap-2">
        @if ($message->phone)
            <a href="https://wa.me/{{ preg_replace('/\D/', '', $message->phone) }}" target="_blank" rel="noopener" class="btn btn-whatsapp"><x-icon name="whatsapp" class="size-4" /> WhatsApp</a>
        @endif
        @if ($message->email)
            <a href="mailto:{{ $message->email }}?subject={{ rawurlencode('Re: '.$message->subject) }}" class="btn btn-secondary"><x-icon name="mail" class="size-4" /> Responder</a>
        @endif
        <form method="POST" action="{{ route('admin.messages.destroy', $message) }}" data-confirm="¿Eliminar este mensaje?">
            @csrf @method('DELETE')
            <button class="btn btn-secondary !text-red-600"><x-icon name="trash" class="size-4" /></button>
        </form>
    </div>
@endsection

@section('content')
    <div class="card max-w-3xl p-6 sm:p-8">
        <dl class="grid gap-4 sm:grid-cols-2">
            <div><dt class="text-xs text-zinc-500 uppercase">Nombre</dt><dd class="font-semibold">{{ $message->name }}</dd></div>
            <div><dt class="text-xs text-zinc-500 uppercase">Fecha</dt><dd>{{ $message->created_at->format('d/m/Y H:i') }}</dd></div>
            @if ($message->email)<div><dt class="text-xs text-zinc-500 uppercase">Email</dt><dd>{{ $message->email }}</dd></div>@endif
            @if ($message->phone)<div><dt class="text-xs text-zinc-500 uppercase">Teléfono</dt><dd>{{ $message->phone }}</dd></div>@endif
            <div class="sm:col-span-2"><dt class="text-xs text-zinc-500 uppercase">Asunto</dt><dd>{{ $message->subject }}</dd></div>
        </dl>

        @if ($message->body)
            <div class="mt-6 rounded-xl bg-zinc-50 p-4 whitespace-pre-line dark:bg-ink-800">{{ $message->body }}</div>
        @endif

        @if ($message->items)
            <table class="mt-6 w-full text-sm">
                <thead class="text-left text-xs text-zinc-500 uppercase"><tr><th class="py-2">Cant.</th><th>Producto</th><th class="text-right">Subtotal</th></tr></thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-ink-700">
                    @foreach ($message->items as $item)
                        <tr><td class="py-2">{{ $item['qty'] }}</td><td>{{ $item['name'] }} <span class="text-zinc-500">({{ $item['code'] }})</span></td><td class="text-right">{{ money($item['price'] * $item['qty']) }}</td></tr>
                    @endforeach
                </tbody>
                <tfoot><tr><td></td><td class="pt-3 font-semibold">Total</td><td class="pt-3 text-right font-display text-lg font-bold">{{ money($message->total) }}</td></tr></tfoot>
            </table>
        @endif
    </div>
@endsection
