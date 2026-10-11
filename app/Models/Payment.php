<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public const STATUSES = [
        'approved' => 'Aprobado',
        'authorized' => 'Autorizado',
        'pending' => 'Pendiente',
        'in_process' => 'En revisión',
        'in_mediation' => 'En disputa',
        'rejected' => 'Rechazado',
        'cancelled' => 'Cancelado',
        'refunded' => 'Reintegrado',
        'charged_back' => 'Contracargo',
    ];

    public const TYPES = [
        'credit_card' => 'Tarjeta de crédito',
        'debit_card' => 'Tarjeta de débito',
        'prepaid_card' => 'Tarjeta prepaga',
        'account_money' => 'Dinero en cuenta de Mercado Pago',
        'ticket' => 'Efectivo (Pago Fácil / Rapipago)',
        'bank_transfer' => 'Transferencia',
        'digital_currency' => 'Mercado Crédito',
    ];

    protected $fillable = [
        'order_id', 'provider', 'provider_id', 'status', 'status_detail', 'payment_type', 'payment_method',
        'card_last_four', 'installments', 'amount', 'amount_refunded', 'approved_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_refunded' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function statusLabel(): string
    {
        return __(self::STATUSES[$this->status] ?? $this->status);
    }

    /** "Tarjeta de crédito VISA •••• 1234 · 3 cuotas" */
    public function methodLabel(): string
    {
        $label = __(self::TYPES[$this->payment_type] ?? 'Mercado Pago');
        if ($this->payment_method && in_array($this->payment_type, ['credit_card', 'debit_card', 'prepaid_card'], true)) {
            $label .= ' '.strtoupper(preg_replace('/^deb/', '', $this->payment_method));
        }
        if ($this->card_last_four) {
            $label .= ' •••• '.$this->card_last_four;
        }
        if ($this->installments > 1) {
            $label .= ' · '.trans_choice(':count cuota|:count cuotas', $this->installments);
        }

        return $label;
    }
}
