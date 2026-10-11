<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Obtiene el código postal cuando el cliente no lo carga:
 * 1) geocodifica la dirección con OpenStreetMap (Nominatim), que suele devolver el CP en Argentina;
 * 2) si no, usa el CP de la capital de la provincia (salvo Buenos Aires, donde varía demasiado).
 */
class PostalCodeResolver
{
    public const PROVINCES = [
        'Ciudad Autónoma de Buenos Aires' => '1000', 'Buenos Aires' => null, 'Catamarca' => '4700', 'Chaco' => '3500',
        'Chubut' => '9103', 'Córdoba' => '5000', 'Corrientes' => '3400', 'Entre Ríos' => '3100', 'Formosa' => '3600',
        'Jujuy' => '4600', 'La Pampa' => '6300', 'La Rioja' => '5300', 'Mendoza' => '5500', 'Misiones' => '3300',
        'Neuquén' => '8300', 'Río Negro' => '8500', 'Salta' => '4400', 'San Juan' => '5400', 'San Luis' => '5700',
        'Santa Cruz' => '9400', 'Santa Fe' => '3000', 'Santiago del Estero' => '4200',
        'Tierra del Fuego' => '9410', 'Tucumán' => '4000',
    ];

    public function resolve(?string $postalCode, ?string $address, ?string $city, ?string $province): ?string
    {
        if (filled($postalCode)) {
            return trim($postalCode);
        }
        if (blank($city) || blank($province)) {
            return null;
        }

        return $this->geocode((string) $address, $city, $province) ?? (self::PROVINCES[$province] ?? null);
    }

    private function geocode(string $address, string $city, string $province): ?string
    {
        $key = 'cp:'.md5(Str::lower("{$address}|{$city}|{$province}"));

        return Cache::remember($key, now()->addDays(30), function () use ($address, $city, $province) {
            try {
                $res = Http::timeout(5)
                    ->withHeaders(['User-Agent' => 'TNLComputer/1.0 (+'.config('app.url').')'])
                    ->get('https://nominatim.openstreetmap.org/search', [
                        'street' => $address, 'city' => $city, 'state' => $province === 'Ciudad Autónoma de Buenos Aires' ? 'Buenos Aires' : $province,
                        'country' => 'Argentina', 'format' => 'jsonv2', 'addressdetails' => 1, 'limit' => 1,
                    ]);
                $cp = $res->ok() ? ($res->json('0.address.postcode') ?? null) : null;

                return $cp && preg_match('/\d{4}/', $cp, $m) ? $m[0] : null;
            } catch (\Throwable) {
                return null;
            }
        });
    }
}
