<?php

namespace Database\Seeders;

use App\Support\ProductCsvImporter;
use Illuminate\Database\Seeder;

/**
 * Catálogo y precios de la lista vigente (database/data/catalogo.csv, generada desde la lista del proveedor).
 * Para actualizar precios más adelante: /admin/productos → "Importar lista" o `php artisan tnl:importar-csv`.
 */
class CatalogSeeder extends Seeder
{
    public function run(ProductCsvImporter $importer): void
    {
        $file = database_path('data/catalogo.csv');
        if (! is_file($file)) {
            $this->command?->warn('No existe database/data/catalogo.csv: no se cargan productos.');

            return;
        }

        $result = $importer->import($file, (float) config('tnl.markup'), deactivateMissing: true);
        $this->command?->info("Catálogo: {$result['created']} nuevos, {$result['updated']} actualizados, {$result['skipped']} salteados.");
    }
}
