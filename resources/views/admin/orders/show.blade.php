@extends('layouts.admin')

@section('title', 'Pedido '.$order->number())

@section('actions')
    <div class="flex flex-wrap gap-2">
        <a href="https://wa.me/{{ preg_replace('/\D/', '', $order->phone) }}" target="_blank" rel="noopener" class="btn btn-whatsapp"><x-icon name="whatsapp" class="size-4" /> WhatsApp</a>
        <a href="mailto:{{ $order->email }}?subject={{ rawurlencode('Tu pedido '.$order->number()) }}" class="btn btn-secondary"><x-icon name="mail" class="size-4" /> Email</a>
        <a href="{{ route($order->locale === 'en' ? 'en.orders.show' : 'orders.show', $order) }}" target="_blank" class="btn btn-secondary"><x-icon name="external" class="size-4" /> Ver como cliente</a>
    </div>
@endsection

@section('content')
    <div class="grid gap-6 xl:grid-cols-[1fr_380px]">
        <div class="space-y-6">
            <div class="card p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm text-zinc-500">Creado {{ $order->created_at->format('d/m/Y H:i') }} · {{ $order->locale === 'en' ? 'en inglés' : 'en español' }} · IP {{ $order->ip }}</p>
                    @include('admin.orders.badge', ['order' => $order])
                </div>
                <dl class="mt-5 grid gap-4 text-sm sm:grid-cols-2">
                    <div><dt class="text-xs text-zinc-500 uppercase">Cliente</dt><dd class="font-semibold">{{ $order->customer_name }}</dd></div>
                    <div><dt class="text-xs text-zinc-500 uppercase">DNI / CUIT</dt><dd>{{ $order->dni ?: '—' }}</dd></div>
                    <div><dt class="text-xs text-zinc-500 uppercase">Email</dt><dd>{{ $order->email }}</dd></div>
                    <div><dt class="text-xs text-zinc-500 uppercase">Teléfono</dt><dd>{{ $order->phone }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-xs text-zinc-500 uppercase">Entrega</dt>
                        <dd>{{ $order->shipping_zone_name }}@unless ($order->isPickup()) — {{ $order->address }}, {{ $order->city }}, {{ $order->province }} (CP {{ $order->postal_code }})@endunless</dd></div>
                    @if ($order->notes)<div class="sm:col-span-2"><dt class="text-xs text-zinc-500 uppercase">Comentarios</dt><dd class="whitespace-pre-line">{{ $order->notes }}</dd></div>@endif
                </dl>
            </div>

            <div class="card overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-zinc-100 text-left text-xs text-zinc-500 uppercase dark:border-ink-700"><tr><th class="px-5 py-3">Cant.</th><th>Producto</th><th class="text-right">Unitario</th><th class="px-5 text-right">Subtotal</th></tr></thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-ink-700">
                        @foreach ($order->items as $item)
                            <tr><td class="px-5 py-2.5">{{ $item->qty }}</td><td>{{ $item->name }} <span class="text-xs text-zinc-500">({{ $item->code }})</span></td><td class="text-right">{{ money($item->unit_price) }}</td><td class="px-5 text-right">{{ money($item->subtotal) }}</td></tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t border-zinc-100 dark:border-ink-700">
                        <tr><td></td><td class="pt-3 text-zinc-500">Envío ({{ $order->shipping_zone_name }})</td><td></td><td class="px-5 pt-3 text-right">{{ money($order->shipping_cost) }}</td></tr>
                        <tr><td></td><td class="py-3 font-semibold">Total</td><td></td><td class="px-5 py-3 text-right font-display text-lg font-bold">{{ money($order->total) }}</td></tr>
                    </tfoot>
                </table>
            </div>

            <div class="card p-6">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="font-sans text-base font-semibold">Pagos en Mercado Pago</h2>
                    @if ($order->payments->isNotEmpty())
                        <form method="POST" action="{{ route('admin.orders.refresh', $order) }}">@csrf<button class="text-sm font-semibold text-brand-600 hover:underline">Volver a consultar</button></form>
                    @endif
                </div>
                @forelse ($order->payments as $payment)
                    <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-1 border-t border-zinc-100 pt-4 text-sm dark:border-ink-700">
                        <span class="flex-1">{{ $payment->methodLabel() }}</span>
                        <span class="chip">{{ $payment->statusLabel() }}@if ($payment->status_detail) · {{ $payment->status_detail }}@endif</span>
                        <span class="font-semibold">{{ money($payment->amount) }}</span>
                        <a href="https://www.mercadopago.com.ar/activities?q={{ $payment->provider_id }}" target="_blank" rel="noopener" class="w-full text-xs text-brand-600 hover:underline">Operación #{{ $payment->provider_id }} — ver en Mercado Pago</a>
                    </div>
                @empty
                    <p class="mt-3 text-sm text-zinc-500">Sin pagos todavía.</p>
                @endforelse
            </div>
        </div>

        <aside class="space-y-6">
            @if (in_array($order->status, ['pending', 'rejected', 'in_process']))
                <div class="card p-6">
                    <h2 class="font-sans text-base font-semibold">Cobro con link de pago</h2>
                    <p class="mt-1 text-xs text-zinc-500">Creá el link en Mercado Pago (Link de pago) por {{ money($order->total) }}, pegalo acá y mandáselo al cliente. En la página del pedido, el botón "Pagar" lo usa.</p>
                    <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="mt-4 space-y-3">
                        @csrf @method('PUT')
                        <x-field name="payment_link" label="Link de pago" type="url" placeholder="https://mpago.la/..." :value="$order->payment_link" />
                        <button name="action" value="payment_link" class="btn btn-secondary !py-2">Guardar link</button>
                    </form>
                    @if ($link = $order->manualPaymentLink())
                        @if ($order->usesGeneralPaymentLink())
                            <p class="mt-3 text-xs text-amber-700 dark:text-amber-300">Sin link propio: se usa el link general del negocio y el mensaje le indica al cliente el monto a pagar.</p>
                        @endif
                        <div class="mt-4 flex flex-wrap gap-2 border-t border-zinc-100 pt-4 dark:border-ink-700">
                            <a href="https://wa.me/{{ preg_replace('/\D/', '', $order->phone) }}?text={{ rawurlencode($order->paymentLinkMessage()) }}" target="_blank" rel="noopener" class="btn btn-whatsapp !py-2"><x-icon name="whatsapp" class="size-4" /> Enviar por WhatsApp</a>
                            <a href="mailto:{{ $order->email }}?subject={{ rawurlencode('Link de pago de tu pedido '.$order->number()) }}&body={{ rawurlencode($order->paymentLinkMessage()) }}" class="btn btn-secondary !py-2"><x-icon name="mail" class="size-4" /> Enviar por email</a>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="mt-4 border-t border-zinc-100 pt-4 dark:border-ink-700" data-confirm="¿Marcar el pedido como pagado? Hacelo cuando veas el cobro acreditado en Mercado Pago.">
                        @csrf @method('PUT')
                        <button name="action" value="paid" class="btn btn-primary !py-2">Marcar como pagado</button>
                    </form>
                </div>
            @endif

            <div class="card p-6">
                <h2 class="font-sans text-base font-semibold">Gestión</h2>
                <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="mt-4 space-y-3">
                    @csrf @method('PUT')
                    <x-field name="tracking_code" label="Código de seguimiento" :value="$order->tracking_code" />
                    <div class="flex flex-wrap gap-2">
                        <button name="action" value="tracking" class="btn btn-secondary !py-2">Guardar código</button>
                        @if (in_array($order->status, ['paid']))
                            <button name="action" value="ship" class="btn btn-primary !py-2">{{ $order->isPickup() ? 'Marcar listo / retirado' : 'Marcar enviado' }}</button>
                        @endif
                        @if ($order->status === 'shipped')
                            <button name="action" value="deliver" class="btn btn-primary !py-2">Marcar entregado</button>
                        @endif
                    </div>
                </form>
                @if (! in_array($order->status, ['cancelled', 'delivered']))
                    <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="mt-4 border-t border-zinc-100 pt-4 dark:border-ink-700" data-confirm="¿Cancelar este pedido? Si ya está pagado, el reintegro se hace desde Mercado Pago.">
                        @csrf @method('PUT')
                        <button name="action" value="cancel" class="text-sm font-semibold text-red-600 hover:underline">Cancelar pedido</button>
                    </form>
                @endif
                <p class="mt-4 text-xs text-zinc-500">Los reintegros se hacen desde Mercado Pago; al hacerlo, el pedido pasa solo a "Reintegrado".</p>
            </div>

            <div class="card p-6 text-sm">
                <h2 class="font-sans text-base font-semibold">Historial</h2>
                <ul class="mt-3 space-y-1.5 text-zinc-600 dark:text-zinc-400">
                    <li>Creado: {{ $order->created_at->format('d/m/Y H:i') }}</li>
                    @if ($order->paid_at)<li>Pagado: {{ $order->paid_at->format('d/m/Y H:i') }}</li>@endif
                    @if ($order->shipped_at)<li>Enviado: {{ $order->shipped_at->format('d/m/Y H:i') }}</li>@endif
                    @if ($order->delivered_at)<li>Entregado: {{ $order->delivered_at->format('d/m/Y H:i') }}</li>@endif
                </ul>
            </div>
        </aside>
    </div>
@endsection
