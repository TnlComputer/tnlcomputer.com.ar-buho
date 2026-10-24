<?php

namespace App\Console\Commands;

use App\Support\ProductCsvImporter;
use Illuminate\Console\Command;

class ImportCsv extends Command
{
    protected $signature = 'tnl:importar-csv
        {archivo* : Uno o más CSV}
        {--ajuste= : Porcentaje a sumar a los precios (por defecto TNL_MARKUP, hoy 20%)}
        {--desactivar : Oculta los productos que no estén en los CSV}';

    protected $description = 'Crea o actualiza productos y precios desde archivos CSV (ver App\Support\ProductCsvImporter)';

    public function handle(ProductCsvImporter $importer): int
    {
        $result = $importer->import($this->argument('archivo'), (float) ($this->option('ajuste') ?? config('tnl.markup')), (bool) $this->option('desactivar'));

        foreach (array_slice($result['errors'], 0, 20) as $error) {
            $this->warn($error);
        }
        $this->info("{$result['created']} nuevos, {$result['updated']} actualizados, {$result['skipped']} salteados, {$result['deactivated']} ocultados.");

        return self::SUCCESS;
    }
}
