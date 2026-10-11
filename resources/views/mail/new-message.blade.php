<x-mail::message>
# {{ \App\Models\Message::TYPES[$msg->type] ?? $msg->type }} desde la web

**Nombre:** {{ $msg->name }}
@if ($msg->phone)
**Teléfono:** {{ $msg->phone }}
@endif
@if ($msg->email)
**Email:** {{ $msg->email }}
@endif
**Asunto:** {{ $msg->subject }}
@if ($msg->locale === 'en')
**Idioma:** inglés (escribió desde la versión en inglés del sitio)
@endif

@if ($msg->body)
<x-mail::panel>
{{ $msg->body }}
</x-mail::panel>
@endif

@if ($msg->items)
<x-mail::table>
| Cant. | Producto | Código | Subtotal |
|:-----:|:---------|:-------|---------:|
@foreach ($msg->items as $item)
| {{ $item['qty'] }} | {{ $item['name'] }} | {{ $item['code'] }} | {{ money($item['price'] * $item['qty']) }} |
@endforeach
| | **Total** | | **{{ money($msg->total) }}** |
</x-mail::table>
@endif

<x-mail::button :url="route('admin.messages.show', $msg)">
Ver en el panel
</x-mail::button>
</x-mail::message>
