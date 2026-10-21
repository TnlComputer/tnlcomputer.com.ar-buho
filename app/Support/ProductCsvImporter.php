<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Importa/actualiza productos desde un CSV (por ejemplo la lista de un proveedor).
 *
 * Columnas reconocidas (encabezado obligatorio, el orden no importa, acepta nombres en español):
 *   code|codigo, name|nombre|detalle, category|rubro|familia, price|precio, stock, image|imagen, description|descripcion,
 *   images|imagenes (fotos adicionales, separadas con "|")
 * Separador coma o punto y coma. Precio "1.234,56", "1234.56" o "1234".
 */
class ProductCsvImporter
{
    private const ALIASES = [
        'code' => ['code', 'codigo', 'código', 'sku'],
        'name' => ['name', 'nombre', 'detalle', 'descripcion corta'],
        'category' => ['category', 'rubro', 'familia', 'categoria', 'categoría'],
        'price' => ['price', 'precio', 'precio final', 'pvp'],
        'stock' => ['stock'],
        'image' => ['image', 'imagen'],
        'images' => ['images', 'imagenes', 'imágenes'],
        'description' => ['description', 'descripcion', 'descripción'],
    ];

    private array $categories = [];

    private ?array $english = null;

    /**
     * @param  float  $adjustPercent  ajuste sobre el precio del CSV (ej. 10 = +10%, -5 = -5%)
     * @param  bool  $deactivateMissing  oculta los productos activos que no vienen en el CSV
     * @return array{created: int, updated: int, skipped: int, deactivated: int, errors: array<int, string>}
     */
    public function import(string|array $paths, float $adjustPercent = 0, bool $deactivateMissing = false): array
    {
        $result = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'deactivated' => 0, 'errors' => []];
        $seen = [];

        DB::transaction(function () use ($paths, $adjustPercent, &$result, &$seen) {
            foreach ((array) $paths as $path) {
                $this->importFile($path, $adjustPercent, $result, $seen);
            }
        });

        if ($deactivateMissing && $seen) {
            $result['deactivated'] = Product::where('active', true)->whereNotIn('id', $seen)->update(['active' => false]);
        }

        return $result;
    }

    private function importFile(string $path, float $adjustPercent, array &$result, array &$seen): void
    {
        $handle = fopen($path, 'r');
        if (! $handle) {
            throw new RuntimeException("No se pudo abrir {$path}");
        }

        $first = (string) fgets($handle);
        if (str_starts_with($first, "\u{FEFF}")) { // BOM de Excel
            $first = substr($first, 3);
        }
        $delimiter = substr_count($first, ';') > substr_count($first, ',') ? ';' : ',';
        $map = $this->mapHeader(str_getcsv(trim($first), $delimiter));

        foreach (['code', 'name', 'price'] as $required) {
            if (! isset($map[$required])) {
                fclose($handle);
                throw new RuntimeException(basename($path).": falta la columna \"{$required}\" en el encabezado.");
            }
        }

        $line = 1;
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $line++;
            if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            $get = fn (string $key) => isset($map[$key]) ? trim((string) ($row[$map[$key]] ?? '')) : null;
            $code = $get('code');
            $name = $get('name');
            $price = $this->parsePrice($get('price'));

            if ($code === '' || $name === '' || $price === null) {
                $result['skipped']++;
                $result['errors'][] = basename($path).", línea {$line}: falta código, nombre o precio válido.";

                continue;
            }

            $product = Product::firstOrNew(['code' => mb_substr($code, 0, 60)]);
            $exists = $product->exists;

            // En los productos que ya existen solo se actualiza lo que cambia con cada lista (precio, stock, foto).
            // El nombre, el rubro y la descripción se corrigen en el panel y la lista del proveedor no los pisa.
            $product->fill(array_filter([
                'name' => $exists ? null : mb_substr($name, 0, 255),
                'price' => round($price * (1 + $adjustPercent / 100)),
                'stock' => $get('stock') ?: ($exists ? null : 'DISPONIBLE'),
                'category_id' => ! $exists && ($category = $get('category')) ? $this->category($category)->id : null,
                'image' => $get('image') ?: null,
                'images' => array_values(array_filter(array_map('trim', explode('|', (string) $get('images'))))) ?: null,
                'description' => $exists ? null : ($get('description') ?: null),
            ], fn ($v) => $v !== null));
            $product->active = true;
            $product->save();

            $seen[] = $product->id;
            $result[$exists ? 'updated' : 'created']++;
        }

        fclose($handle);
    }

    private function mapHeader(array $header): array
    {
        $map = [];
        foreach ($header as $index => $column) {
            $column = Str::lower(trim((string) $column, " \t\"'"));
            foreach (self::ALIASES as $key => $aliases) {
                if (! isset($map[$key]) && in_array($column, $aliases, true)) {
                    $map[$key] = $index;
                }
            }
        }

        return $map;
    }

    /** "1.234.567,89" → 1234567.89 · "1234567.89" → 1234567.89 · "$ 12.500" → 12500 */
    public function parsePrice(?string $value): ?float
    {
        $value = preg_replace('/[^\d.,\-]/', '', (string) $value);
        if ($value === '' || $value === '-') {
            return null;
        }

        $lastComma = strrpos($value, ',');
        $lastDot = strrpos($value, '.');

        if ($lastComma !== false && ($lastDot === false || $lastComma > $lastDot)) {
            // La coma es el decimal (formato argentino)
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif ($lastDot !== false && substr_count($value, '.') > 1) {
            $value = str_replace('.', '', $value); // 1.234.567
        } elseif ($lastDot !== false && strlen($value) - $lastDot - 1 === 3 && $lastComma === false) {
            $value = str_replace('.', '', $value); // 12.500 = doce mil quinientos
        } else {
            $value = str_replace(',', '', $value);
        }

        return is_numeric($value) && (float) $value >= 0 ? (float) $value : null;
    }

    private function category(string $name): Category
    {
        $slug = Str::slug($name);
        $this->english ??= require database_path('data/rubros-en.php');

        return $this->categories[$slug] ??= Category::firstOrCreate(
            ['slug' => $slug],
            ['name' => $name, 'name_en' => $this->english[$slug] ?? null],
        );
    }
}
