<?php

namespace App\Support;

use App\Models\Product;

/**
 * Marca, número de parte (MPN) y código de barras (GTIN/EAN) de un producto, sacados del nombre y el código
 * que manda el proveedor. Google Merchant Center los usa para relacionar el producto con las búsquedas.
 *
 * De dónde salen:
 * - MPN: el código entre corchetes al principio del nombre ("[KF552C40BBA-16] Memoria..."), o entre paréntesis
 *   al final ("... (SA400S37/480G)"), o el código del producto cuando es un número de parte del fabricante.
 * - GTIN: un número de 8, 12, 13 o 14 dígitos entre paréntesis con dígito verificador válido.
 * - Marca: la primera marca conocida que aparece en el nombre. Los productos alternativos o genéricos van sin
 *   marca: poner la del original sería engañoso (y Google lo rechaza).
 */
class ProductIdentifiers
{
    /** Alias => marca. El orden no importa: gana la que aparece primero en el nombre. */
    private const BRANDS = [
        'Western Digital' => 'Western Digital', 'WD' => 'Western Digital', 'Seagate' => 'Seagate',
        'Kingston' => 'Kingston', 'Fury' => 'Kingston', 'HyperX' => 'HyperX', 'Corsair' => 'Corsair',
        'Crucial' => 'Crucial', 'ADATA' => 'ADATA', 'XPG' => 'ADATA', 'Patriot' => 'Patriot', 'Lexar' => 'Lexar',
        'Hiksemi' => 'Hiksemi', 'SanDisk' => 'SanDisk', 'Puskill' => 'Puskill', 'Memox' => 'Memox', 'Samsung' => 'Samsung',
        'AMD' => 'AMD', 'Ryzen' => 'AMD', 'Intel' => 'Intel', 'Pentium' => 'Intel', 'Celeron' => 'Intel',
        'Core i3' => 'Intel', 'Core i5' => 'Intel', 'Core i7' => 'Intel', 'Core i9' => 'Intel', 'Core Ultra' => 'Intel',
        'ASUS' => 'ASUS', 'ROG' => 'ASUS', 'Gigabyte' => 'Gigabyte', 'Aorus' => 'Gigabyte', 'MSI' => 'MSI',
        'Zotac' => 'Zotac', 'PNY' => 'PNY', 'ASRock' => 'ASRock', 'PowerColor' => 'PowerColor',
        'Cooler Master' => 'Cooler Master', 'CM' => 'Cooler Master', 'Thermaltake' => 'Thermaltake',
        'Gamemax' => 'Gamemax', 'Sentey' => 'Sentey', 'Aconcawa' => 'Aconcawa', 'Logitech' => 'Logitech',
        'Redragon' => 'Redragon', 'Genius' => 'Genius', 'Aula' => 'Aula', 'Noganet' => 'Noganet', 'Nisuta' => 'Nisuta',
        'Netmak' => 'Netmak', 'Netmack' => 'Netmak', 'TP-Link' => 'TP-Link', 'Mercusys' => 'Mercusys',
        'Lenovo' => 'Lenovo', 'HP' => 'HP', 'Dell' => 'Dell', 'Acer' => 'Acer', 'Epson' => 'Epson', 'Brother' => 'Brother',
        'Canon' => 'Canon', 'AOC' => 'AOC', 'LG' => 'LG', 'Philips' => 'Philips', 'Zowie' => 'Zowie', 'Xiaomi' => 'Xiaomi',
        'Motorola' => 'Motorola', 'Apple' => 'Apple', 'PXN' => 'PXN', 'Westinghouse' => 'Westinghouse',
        'Encore' => 'Encore', 'Kanji' => 'Kanji', 'Maono' => 'Maono', 'Kieslect' => 'Kieslect', 'Fukuoka' => 'Fukuoka',
        'Lyonn' => 'Lyonn',
    ];

    /** Cosas entre paréntesis que no son números de parte: zócalos, capacidades, colores, versiones. */
    private const NOT_MPN = '/^(LGA ?\d+|AM\d|\d+(\.\d+)?\s?(GB|TB|MB|MHZ|GHZ|W|HZ|MM|M|CM|L|ML|V|MAH|"|PULGADAS)|[SC]\/?COOLER|BOX|TRAY|OEM|BULK|WHITE|BLACK|NEGRO|BLANCO|RGB|ARGB)$/i';

    /** @return array{brand: ?string, mpn: ?string, gtin: ?string} */
    public static function for(Product $product): array
    {
        $name = $product->name;
        $gtin = $mpn = null;

        if (preg_match('/^\[([^\]]+)\]/', $name, $m)) {
            $mpn = self::mpn($m[1]);
        }

        // Paréntesis del final (puede haber dos: "(GM300) (6950376776402)") o "| CODIGO".
        preg_match('/(?:\(([^()]+)\)\s*)+$/', $name, $tail);
        $candidates = preg_match_all('/\(([^()]+)\)/', $tail[0] ?? '', $all) ? $all[1] : [];
        if (preg_match('/\|\s*([A-Z0-9][A-Z0-9\/-]+)\s*$/', $name, $m)) {
            $candidates[] = $m[1];
        }
        foreach (array_reverse($candidates) as $candidate) {
            $candidate = trim($candidate);
            if (self::validGtin($candidate)) {
                $gtin ??= $candidate;
            } elseif (! preg_match('/^\d+$/', $candidate)) {
                $mpn ??= self::mpn($candidate);
            }
        }

        // El código propio sirve cuando es un número de parte (WD102PURP, BX8071514700F), no un número interno
        // del proveedor (41308, TN42085) ni uno armado con el nombre (los de más de 25 caracteres).
        $code = $product->code;
        if (! $mpn && ! str_starts_with($name, '[') && ! preg_match('/^(\d+|TN\d+)$/', $code)
            && strlen($code) <= 25 && preg_match('/^[A-Z0-9][A-Z0-9\/. -]*$/', $code)) {
            $mpn = self::mpn($code);
        }

        return ['brand' => self::brand($name), 'mpn' => $mpn, 'gtin' => $gtin];
    }

    /** ¿Alcanza para que Google identifique el producto? (GTIN, o marca + MPN) */
    public static function identified(array $ids): bool
    {
        return $ids['gtin'] !== null || ($ids['brand'] !== null && $ids['mpn'] !== null);
    }

    private static function mpn(string $value): ?string
    {
        $value = trim($value);
        if (mb_strlen($value) < 3 || mb_strlen($value) > 40 || substr_count($value, ' ') > 1 || preg_match(self::NOT_MPN, $value)) {
            return null;
        }

        return preg_match('/\d/', $value) && preg_match('/[A-Za-z]/', $value) ? $value : null;
    }

    private static function brand(string $name): ?string
    {
        if (preg_match('/alternativ|compatible|gen[eé]ric/iu', $name)) {
            return null;
        }

        $best = null;
        foreach (self::BRANDS as $alias => $brand) {
            if (preg_match('/(?<![\p{L}\d])'.preg_quote($alias, '/').'(?![\p{L}\d])/iu', $name, $m, PREG_OFFSET_CAPTURE)
                && ($best === null || $m[0][1] < $best[0])) {
                $best = [$m[0][1], $brand];
            }
        }

        return $best[1] ?? null;
    }

    private static function validGtin(string $value): bool
    {
        if (! preg_match('/^(\d{8}|\d{12,14})$/', $value)) {
            return false;
        }

        $digits = array_reverse(str_split(substr($value, 0, -1)));
        $sum = 0;
        foreach ($digits as $i => $digit) {
            $sum += (int) $digit * ($i % 2 === 0 ? 3 : 1);
        }

        return (10 - $sum % 10) % 10 === (int) substr($value, -1);
    }
}
