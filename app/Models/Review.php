<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Reseña de un producto hecha por quien lo compró. Se publica cuando se aprueba en el panel. */
class Review extends Model
{
    public const PENDING = 'pending';

    public const APPROVED = 'approved';

    public const REJECTED = 'rejected';

    public const STATUSES = [
        self::PENDING => 'Pendiente',
        self::APPROVED => 'Publicada',
        self::REJECTED => 'Rechazada',
    ];

    protected $fillable = ['product_id', 'order_id', 'author', 'rating', 'comment', 'status', 'ip', 'approved_at'];

    protected $casts = [
        'rating' => 'integer',
        'approved_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function scopeApproved(Builder $query): void
    {
        $query->where('status', self::APPROVED);
    }

    /** "Ana María Pérez" → "Ana P.": en la tienda no se publica el nombre completo del cliente. */
    public static function authorFrom(string $fullName): string
    {
        $parts = preg_split('/\s+/', trim($fullName)) ?: [];
        $first = mb_convert_case(mb_strtolower($parts[0] ?? ''), MB_CASE_TITLE);
        $last = count($parts) > 1 ? mb_strtoupper(mb_substr(end($parts), 0, 1)).'.' : '';

        return mb_substr(trim("{$first} {$last}") ?: __('Cliente'), 0, 60);
    }
}
