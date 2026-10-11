<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    public const SIN_STOCK = 'SIN STOCK';

    protected $fillable = [
        'code', 'category_id', 'name', 'description', 'price', 'stock', 'image', 'featured', 'active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'featured' => 'boolean',
        'active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** Productos que se muestran en la tienda. */
    public function scopeVisible(Builder $query): void
    {
        $query->where('active', true)
            ->where(fn ($q) => $q->whereNull('stock')->orWhere('stock', '<>', self::SIN_STOCK));
    }

    public function scopeSearch(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);
        if ($term === '') {
            return;
        }
        $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%"));
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    /** "DISPONIBLE" → "Disponible" / "Available". */
    public function stockLabel(): string
    {
        return __(ucfirst(mb_strtolower((string) $this->stock)));
    }

    /**
     * Ruta relativa a public/: subidas desde el panel (uploads/productos/...), catálogo (img/productos/catalogo/...)
     *. Sin imagen asignada no se adivina por código: el proveedor
     * reutiliza códigos para productos distintos.
     */
    public function imageUrl(): string
    {
        $path = $this->image;

        return $path && is_file(public_path($path))
            ? asset(implode('/', array_map('rawurlencode', explode('/', $path))))
            : asset('img/sin-imagen.svg');
    }
}
