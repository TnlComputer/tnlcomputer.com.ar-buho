<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    public const PENDING = 'pending';          // creado, esperando pago
    public const IN_PROCESS = 'in_process';    // pago en revisión / acreditándose
    public const PAID = 'paid';
    public const REJECTED = 'rejected';        // todos los intentos rechazados (puede reintentar)
    public const SHIPPED = 'shipped';
    public const DELIVERED = 'delivered';
    public const CANCELLED = 'cancelled';
    public const REFUNDED = 'refunded';

    public const STATUSES = [
        self::PENDING => 'Esperando pago',
        self::IN_PROCESS => 'Pago en proceso',
        self::PAID => 'Pagado',
        self::REJECTED => 'Pago rechazado',
        self::SHIPPED => 'Enviado',
        self::DELIVERED => 'Entregado',
        self::CANCELLED => 'Cancelado',
        self::REFUNDED => 'Reintegrado',
    ];

    protected $fillable = [
        'token', 'status', 'customer_name', 'email', 'phone', 'dni', 'shipping_zone_id', 'shipping_zone_name',
        'postal_code', 'address', 'city', 'province', 'notes', 'subtotal', 'shipping_cost', 'total', 'currency',
        'mp_preference_id', 'payment_link', 'tracking_code', 'locale', 'ip', 'paid_at', 'shipped_at', 'delivered_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->token ??= Str::random(40);
            $order->status ??= self::PENDING;
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest();
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }

    public function getRouteKeyName(): string
    {
        return 'token';
    }

    /** Número visible para el cliente: TNL-000123. */
    public function number(): string
    {
        return 'TNL-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function statusLabel(): string
    {
        return __(self::STATUSES[$this->status] ?? $this->status);
    }

    public function isPickup(): bool
    {
        return blank($this->address);
    }

    /** ¿Se puede (re)intentar pagar? */
    public function canPay(): bool
    {
        return in_array($this->status, [self::PENDING, self::REJECTED], true);
    }

    /**
     * Link de pago manual: el cargado en el pedido o, si no hay, el link fijo del negocio (sin monto).
     * Se usa mientras no se pueda generar la preferencia automática de Mercado Pago.
     */
    public function manualPaymentLink(): ?string
    {
        return $this->payment_link ?: (config('tnl.payment_link') ?: null);
    }

    /** El link manual es el general del negocio: el cliente tiene que escribir el monto al pagar. */
    public function usesGeneralPaymentLink(): bool
    {
        return blank($this->payment_link) && filled(config('tnl.payment_link'));
    }

    /** Mensaje para mandarle al cliente el link de pago (WhatsApp o email). */
    public function paymentLinkMessage(): string
    {
        $lines = [
            "¡Hola {$this->customer_name}! Te paso el link para pagar tu pedido {$this->number()} por ".money($this->total).' con Mercado Pago:',
            $this->manualPaymentLink(),
        ];
        if ($this->usesGeneralPaymentLink()) {
            $lines[] = 'Al pagar, ingresá el monto '.money($this->total).' y en el detalle el número de pedido '.$this->number().'.';
        }
        $lines[] = 'Podés seguir el estado del pedido acá: '.route($this->locale === 'en' ? 'en.orders.show' : 'orders.show', $this);
        $lines[] = '¡Gracias! TNL Computer';

        return implode("\n\n", $lines);
    }

    public function amountApproved(): float
    {
        return (float) $this->payments->where('status', 'approved')
            ->sum(fn (Payment $p) => (float) $p->amount - (float) $p->amount_refunded);
    }

    /**
     * Recalcula el estado a partir de los pagos informados por Mercado Pago.
     * Con dos tarjetas llegan dos pagos aprobados: el pedido queda pagado cuando la suma cubre el total.
     * No toca pedidos ya enviados, entregados o cancelados a mano.
     */
    public function syncStatusFromPayments(): void
    {
        if (in_array($this->status, [self::SHIPPED, self::DELIVERED, self::CANCELLED], true)) {
            return;
        }

        $this->load('payments');
        $approved = $this->amountApproved();
        $total = (float) $this->total;

        $status = match (true) {
            $approved + 0.01 >= $total => self::PAID,
            $this->payments->whereIn('status', ['refunded', 'charged_back'])->isNotEmpty() && $approved <= 0 => self::REFUNDED,
            $this->payments->whereIn('status', ['pending', 'in_process', 'authorized'])->isNotEmpty() || $approved > 0 => self::IN_PROCESS,
            $this->payments->isNotEmpty() => self::REJECTED,
            default => self::PENDING,
        };

        $this->status = $status;
        if ($status === self::PAID && ! $this->paid_at) {
            $this->paid_at = now();
        }
        $this->save();
    }
}
