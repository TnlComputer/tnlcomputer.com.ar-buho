<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /** Rubros del sitio viejo (assets/rubros.json). La importación desde la base vieja los completa. */
    public function run(): void
    {
        $rubros = json_decode(file_get_contents(database_path('data/rubros.json')), true);
        $english = require database_path('data/rubros-en.php');

        foreach ($rubros as $rubro) {
            $slug = Str::slug($rubro['rubro']);
            Category::updateOrCreate(
                ['slug' => $slug],
                ['name' => $rubro['rubro'], 'name_en' => $english[$slug] ?? null, 'legacy_id' => $rubro['id_rubro']],
            );
        }
    }
}
