<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    public const SIN_STOCK = 'SIN STOCK';

    protected $fillable = [
        'code', 'category_id', 'name', 'description', 'specs', 'price', 'stock', 'image', 'featured', 'active',
    ];

    protected $casts = [
        'specs' => 'array',
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

    /** Ítem con el formato de los eventos de e-commerce de Google Analytics. */
    public function gaItem(int $qty = 1): array
    {
        return array_filter([
            'item_id' => $this->code,
            'item_name' => $this->name,
            'item_category' => $this->category?->name,
            'price' => (float) $this->price,
            'quantity' => $qty,
        ], fn ($v) => $v !== null);
    }

    /** Características como texto para el panel: una por línea, "Nombre: valor". */
    public function specsText(): string
    {
        return collect($this->specs ?? [])->map(fn ($s) => "{$s[0]}: {$s[1]}")->implode("\n");
    }

    /**
     * Lee el texto del panel ("Nombre: valor" por línea). Las líneas sin ":" o con algo vacío se ignoran.
     *
     * @return list<array{0: string, 1: string}>|null
     */
    public static function parseSpecs(?string $text): ?array
    {
        $specs = collect(preg_split('/\R/', (string) $text))
            ->map(fn ($line) => array_map('trim', explode(':', $line, 2)))
            ->filter(fn ($s) => count($s) === 2 && $s[0] !== '' && $s[1] !== '')
            ->map(fn ($s) => [mb_substr($s[0], 0, 60), mb_substr($s[1], 0, 200)])
            ->values()->all();

        return $specs ?: null;
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
