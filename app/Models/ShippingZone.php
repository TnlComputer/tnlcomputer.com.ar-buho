<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ShippingZone extends Model
{
    use Translatable;

    protected $fillable = [
        'name', 'name_en', 'eta', 'eta_en', 'postal_ranges', 'price', 'free_from', 'is_pickup', 'active', 'sort',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'free_from' => 'decimal:2',
        'is_pickup' => 'boolean',
        'active' => 'boolean',
    ];

    public function scopeActive(Builder $query): void
    {
        $query->where('active', true)->orderBy('sort');
    }

    /** Costo de envío para un subtotal (gratis si supera free_from). */
    public function costFor(float $subtotal): float
    {
        if ($this->is_pickup || ($this->free_from !== null && $subtotal >= (float) $this->free_from)) {
            return 0.0;
        }

        return (float) $this->price;
    }

    /** @return array<int, array{int, int}> */
    public function ranges(): array
    {
        return collect(preg_split('/[\r\n,;]+/', (string) $this->postal_ranges))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->map(function ($line) {
                [$from, $to] = array_pad(array_map('intval', explode('-', $line, 2)), 2, null);

                return [$from, $to ?: $from];
            })
            ->values()
            ->all();
    }

    /** Código postal argentino: acepta "1425" o CPA "C1425ABC"; devuelve los 4 dígitos. */
    public static function normalizePostalCode(?string $cp): ?int
    {
        return preg_match('/(\d{4})/', (string) $cp, $m) ? (int) $m[1] : null;
    }

    /** Zona de envío (no retiro) que corresponde a un CP. Si ningún rango coincide, la zona sin rangos (resto del país). */
    public static function forPostalCode(?string $cp): ?self
    {
        $number = self::normalizePostalCode($cp);
        if ($number === null) {
            return null;
        }

        $zones = self::active()->where('is_pickup', false)->get();

        foreach ($zones as $zone) {
            foreach ($zone->ranges() as [$from, $to]) {
                if ($number >= $from && $number <= $to) {
                    return $zone;
                }
            }
        }

        return $zones->first(fn (self $zone) => blank($zone->postal_ranges));
    }
}
