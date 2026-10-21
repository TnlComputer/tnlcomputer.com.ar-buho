<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Un cambio de estado de un pedido. Lo crea Order solo, cada vez que cambia el estado. */
class OrderEvent extends Model
{
    public const UPDATED_AT = null;

    public const SOURCES = [
        'admin' => 'Panel',
        'cliente' => 'Cliente',
        'mercadopago' => 'Mercado Pago',
        'sistema' => 'Sistema',
    ];

    protected $fillable = ['order_id', 'user_id', 'source', 'from_status', 'to_status', 'ip'];

    public static function record(Order $order, ?string $from): void
    {
        $request = app()->runningInConsole() ? null : request();

        static::create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'source' => static::currentSource(),
            'from_status' => $from,
            'to_status' => $order->status,
            'ip' => $request?->ip(),
        ]);
    }

    /** De dónde viene el cambio, según quién está logueado o qué ruta lo provocó. */
    private static function currentSource(): string
    {
        if (auth()->check()) {
            return 'admin';
        }
        if (app()->runningInConsole()) {
            return 'sistema';
        }

        // Al volver de Mercado Pago (orders.show con payment_id) el estado lo define el pago, no el cliente.
        return request()->routeIs('webhooks.mercadopago', 'orders.show', 'en.orders.show') ? 'mercadopago' : 'cliente';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sourceLabel(): string
    {
        return self::SOURCES[$this->source] ?? $this->source;
    }
}
