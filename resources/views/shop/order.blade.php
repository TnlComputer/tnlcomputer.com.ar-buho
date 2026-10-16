@extends('layouts.site')

@section('title', __('Pedido :number', ['number' => $order->number()]))

@push('head')<meta name="robots" content="noindex, nofollow">@endpush

@php
    $timeline = [
        [__('Pedido creado'), $order->created_at],
        [__('Pago aprobado'), $order->paid_at],
        [$order->isPickup() ? __('Listo / retirado') : __('Enviado'), $order->shipped_at],
        [__('Entregado'), $order->delivered_at],
    ];
    $tone = match ($order->status) {
        'paid', 'shipped', 'delivered' => 'emerald',
        'rejected', 'cancelled', 'refunded' => 'red',
        default => 'amber',
    };
@endphp

@section('content')
    <div class="container-x py-10">
        @include('shop.partials.steps', ['step' => $order->paid_at ? 4 : 3])

        <div class="mt-8 flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-sm text-zinc-500">{{ __('Pedido') }} {{ $order->number() }} · {{ $order->created_at->translatedFormat('d M Y, H:i') }}</p>
                <h1 class="mt-1 text-3xl font-bold sm:text-4xl">
                    @switch($order->status)
                        @case('paid') {{ __('¡Gracias! Tu pago fue aprobado') }} @break
                        @case('in_process') {{ __('Tu pago se está procesando') }} @break
                        @case('rejected') {{ __('El pago no se pudo completar') }} @break
                        @case('shipped') {{ $order->isPickup() ? __('Tu pedido está listo') : __('Tu pedido está en camino') }} @break
                        @case('delivered') {{ __('Pedido entregado') }} @break
                        @case('cancelled') {{ __('Pedido cancelado') }} @break
                        @default {{ __('Tu pedido espera el pago') }}
                    @endswitch
                </h1>
            </div>
            <span @class([
                'rounded-full px-3 py-1 text-sm font-semibold',
                'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' => $tone === 'emerald',
                'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' => $tone === 'red',
                'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' => $tone === 'amber',
            ])>{{ $order->statusLabel() }}</span>
        </div>

        @if (session('payment_error'))
            <div class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-900 dark:border-red-900 dark:bg-red-950/50 dark:text-red-200" role="alert">{{ session('payment_error') }}</div>
        @endif

        <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_380px]">
            <div class="space-y-6">
                {{-- Estado --}}
                <section class="card p-6">
                    <h2 class="font-sans text-base font-semibold">{{ __('Estado del pedido') }}</h2>
                    <ol class="mt-5 grid gap-4 sm:grid-cols-4">
                        @foreach ($timeline as [$label, $date])
                            <li class="flex gap-3 sm:flex-col">
                                <span @class(['grid size-8 shrink-0 place-items-center rounded-full', 'bg-emerald-600 text-white' => $date, 'border-2 border-zinc-300 text-zinc-400 dark:border-ink-700' => ! $date])>
                                    @if ($date)<x-icon name="check" class="size-4" />@else{{ $loop->iteration }}@endif
                                </span>
                                <div>
                                    <p @class(['text-sm font-semibold', 'text-zinc-900 dark:text-white' => $date, 'text-zinc-500' => ! $date])>{{ $label }}</p>
                                    @if ($date)<p class="text-xs text-zinc-500">{{ $date->translatedFormat('d/m/Y H:i') }}</p>@endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                    @if ($order->tracking_code)
                        <p class="mt-5 rounded-xl bg-zinc-50 p-3 text-sm dark:bg-ink-800">{{ __('Código de seguimiento') }}: <strong class="font-mono">{{ $order->tracking_code }}</strong></p>
                    @endif

                    @if ($order->status === 'in_process')
                        <p class="mt-5 text-sm text-zinc-600 dark:text-zinc-400">{{ __('Mercado Pago está revisando el pago. Suele resolverse en minutos; te avisamos por mail. Podés recargar esta página.') }}</p>
                    @endif

                    @if ($order->canPay())
                        <div class="mt-6 border-t border-zinc-100 pt-5 dark:border-ink-700">
                            @if ($order->status === 'rejected')
                                <p class="mb-4 text-sm text-zinc-600 dark:text-zinc-400">{{ __('No se te cobró nada. Podés intentar con otra tarjeta o medio de pago.') }}</p>
                            @endif
                            {{-- Link cargado a mano en el pedido > preferencia automática > link general del negocio. --}}
                            @if ($order->payment_link || (! $paymentsEnabled && $order->manualPaymentLink()))
                                <a href="{{ $order->manualPaymentLink() }}" target="_blank" rel="noopener" class="btn btn-primary"><x-icon name="shield" class="size-4" /> {{ __('Pagar :amount', ['amount' => money($order->total)]) }}</a>
                                @if ($order->usesGeneralPaymentLink())
                                    <p class="mt-3 text-sm text-zinc-600 dark:text-zinc-400">{{ __('Al pagar, ingresá el monto :amount y en el detalle el número de pedido :number.', ['amount' => money($order->total), 'number' => $order->number()]) }}</p>
                                @endif
                            @else
                                <form method="POST" action="{{ lroute('orders.pay', $order) }}" data-once>
                                    @csrf
                                    <button class="btn btn-primary" data-label="{{ __('Redirigiendo a Mercado Pago…') }}"><x-icon name="shield" class="size-4" /> {{ $order->status === 'rejected' ? __('Reintentar el pago') : __('Pagar :amount', ['amount' => money($order->total)]) }}</button>
                                </form>
                            @endif
                            @include('shop.partials.payment-badges')
                        </div>
                    @endif
                </section>

                @if ($order->status === 'delivered' && config('tnl.google_review'))
                    <section class="card flex flex-wrap items-center gap-4 p-6">
                        <x-icon name="star" class="size-6 shrink-0 text-amber-500" />
                        <div class="min-w-0 flex-1">
                            <h2 class="font-sans text-base font-semibold">{{ __('¿Cómo te fue con tu compra?') }}</h2>
                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ __('Tu opinión en Google nos ayuda mucho y le sirve a otros clientes.') }}</p>
                        </div>
                        <a href="{{ config('tnl.google_review') }}" target="_blank" rel="noopener" class="btn btn-primary">{{ __('Dejar una reseña') }}</a>
                    </section>
                @endif

                {{-- Pagos --}}
                @if ($order->payments->isNotEmpty())
                    <section class="card p-6">
                        <h2 class="font-sans text-base font-semibold">{{ __('Pagos') }}</h2>
                        <ul class="mt-4 divide-y divide-zinc-100 text-sm dark:divide-ink-700">
                            @foreach ($order->payments as $payment)
                                <li class="flex flex-wrap items-center gap-x-4 gap-y-1 py-3">
                                    <span class="min-w-0 flex-1">{{ $payment->methodLabel() }}</span>
                                    <span @class(['chip', '!bg-emerald-100 !text-emerald-800' => $payment->status === 'approved', '!bg-red-100 !text-red-800' => in_array($payment->status, ['rejected', 'cancelled'])])>{{ $payment->statusLabel() }}</span>
                                    <span class="font-semibold">{{ money($payment->amount) }}</span>
                                    <span class="w-full text-xs text-zinc-500">{{ __('Operación Mercado Pago') }} #{{ $payment->provider_id }} · {{ $payment->created_at->translatedFormat('d/m/Y H:i') }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                {{-- Entrega --}}
                <section class="card p-6 text-sm">
                    <h2 class="font-sans text-base font-semibold">{{ __('Entrega') }}</h2>
                    <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div><dt class="text-xs text-zinc-500 uppercase">{{ __('Nombre') }}</dt><dd>{{ $order->customer_name }}</dd></div>
                        <div><dt class="text-xs text-zinc-500 uppercase">{{ __('Contacto') }}</dt><dd>{{ $order->email }} · {{ $order->phone }}</dd></div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs text-zinc-500 uppercase">{{ $order->isPickup() ? __('Retiro') : __('Dirección') }}</dt>
                            <dd>{{ $order->zone?->tr('name') ?? $order->shipping_zone_name }}@unless ($order->isPickup()) — {{ $order->address }}, {{ $order->city }}, {{ $order->province }} ({{ $order->postal_code }})@endunless</dd>
                        </div>
                    </dl>
                </section>
            </div>

            {{-- Detalle --}}
            <aside class="card h-fit p-6">
                <h2 class="font-sans text-base font-semibold">{{ __('Detalle') }}</h2>
                <ul class="mt-4 space-y-3 text-sm">
                    @foreach ($order->items as $item)
                        <li class="flex gap-3">
                            <span class="text-zinc-500">{{ $item->qty }}×</span>
                            <span class="min-w-0 flex-1">{{ $item->name }} <span class="block text-xs text-zinc-500">{{ money($item->unit_price) }} {{ __('c/u') }}</span></span>
                            <span class="font-medium">{{ money($item->subtotal) }}</span>
                        </li>
                    @endforeach
                </ul>
                <dl class="mt-5 space-y-2 border-t border-zinc-100 pt-4 text-sm dark:border-ink-700">
                    <div class="flex justify-between"><dt class="text-zinc-600 dark:text-zinc-400">{{ __('Subtotal') }}</dt><dd>{{ money($order->subtotal) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-zinc-600 dark:text-zinc-400">{{ __('Envío') }}</dt><dd>{{ (float) $order->shipping_cost > 0 ? money($order->shipping_cost) : __('Gratis') }}</dd></div>
                    <div class="flex items-baseline justify-between border-t border-zinc-100 pt-3 dark:border-ink-700">
                        <dt class="font-semibold">{{ __('Total') }}</dt>
                        <dd class="font-display text-2xl font-bold text-zinc-950 dark:text-white">{{ money($order->total) }}</dd>
                    </div>
                    @if ($order->amountApproved() > 0)
                        <div class="flex justify-between text-emerald-700 dark:text-emerald-400"><dt>{{ __('Pagado') }}</dt><dd>{{ money($order->amountApproved()) }}</dd></div>
                    @endif
                </dl>
                <p class="mt-5 text-xs text-zinc-500">{{ __('Guardá esta página: es el comprobante y el seguimiento de tu pedido.') }}</p>
                <a href="{{ whatsapp_url(__('Hola, consulto por mi pedido :number.', ['number' => $order->number()])) }}" target="_blank" rel="noopener" class="btn btn-whatsapp mt-4 w-full"><x-icon name="whatsapp" class="size-4" /> {{ __('Consultar por este pedido') }}</a>
            </aside>
        </div>
    </div>
@endsection
