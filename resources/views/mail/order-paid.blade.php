<x-mail::message>
@if ($admin)
# Venta pagada {{ $order->number() }}

**Cliente:** {{ $order->customer_name }} · {{ $order->email }} · {{ $order->phone }}@if ($order->dni) · DNI {{ $order->dni }}@endif

@if ($order->locale === 'en')
**Idioma:** inglés
@endif
@else
# {{ __('¡Gracias por tu compra, :name!', ['name' => $order->customer_name]) }}

{{ __('Recibimos el pago de tu pedido :number. Ya lo estamos preparando.', ['number' => $order->number()]) }}
@endif

<x-mail::table>
| {{ __('Cant.') }} | {{ __('Producto') }} | {{ __('Subtotal') }} |
|:-:|:--|--:|
@foreach ($order->items as $item)
| {{ $item->qty }} | {{ $item->name }} | {{ money($item->subtotal) }} |
@endforeach
| | {{ __('Envío') }}: {{ $order->shipping_zone_name }} | {{ money($order->shipping_cost) }} |
| | **{{ __('Total') }}** | **{{ money($order->total) }}** |
</x-mail::table>

@if ($order->isPickup())
**{{ __('Entrega') }}:** {{ __('Retiro en el local. Te avisamos cuando esté listo.') }}
@else
**{{ __('Entrega') }}:** {{ $order->address }}, {{ $order->city }}, {{ $order->province }} ({{ $order->postal_code }})
@endif

@foreach ($order->payments->where('status', 'approved') as $payment)
- {{ $payment->methodLabel() }}: {{ money($payment->amount) }}
@endforeach

<x-mail::button :url="$admin ? route('admin.orders.show', $order) : route($order->locale === 'en' ? 'en.orders.show' : 'orders.show', $order)">
{{ $admin ? 'Ver en el panel' : __('Ver el estado de tu pedido') }}
</x-mail::button>

@unless ($admin)
{{ __('¿Dudas? Respondé este mail o escribinos por WhatsApp al :phone.', ['phone' => config('tnl.whatsapp_label')]) }}
@endunless
</x-mail::message>
