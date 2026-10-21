<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Support\ProductCsvImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCsvImporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_parses_argentine_and_us_price_formats(): void
    {
        $importer = new ProductCsvImporter();

        $this->assertSame(1234567.89, $importer->parsePrice('$ 1.234.567,89'));
        $this->assertSame(69769.0, $importer->parsePrice('$ 69,769.00'));
        $this->assertSame(12500.0, $importer->parsePrice('12.500'));
        $this->assertSame(545.49, $importer->parsePrice('545.49'));
        $this->assertNull($importer->parsePrice(''));
    }

    public function test_imports_creates_updates_adjusts_and_deactivates(): void
    {
        Product::create(['code' => 'OLD', 'name' => 'Viejo', 'price' => 1]);
        Product::create(['code' => 'A1', 'name' => 'Mouse', 'price' => 1000]);

        $file = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($file, "\u{FEFF}codigo;nombre;rubro;precio\nA1;Mouse Logitech;Mouses;\"2.000,00\"\nB2;Teclado;Teclados;3000\n;sin codigo;x;1\n");

        $result = (new ProductCsvImporter())->import($file, adjustPercent: 10, deactivateMissing: true);

        $this->assertSame(['created' => 1, 'updated' => 1, 'skipped' => 1, 'deactivated' => 1], array_intersect_key($result, array_flip(['created', 'updated', 'skipped', 'deactivated'])));
        $this->assertEquals(2200, Product::where('code', 'A1')->value('price'));
        // El nombre de un producto existente no se pisa (se corrige en el panel).
        $this->assertSame('Mouse', Product::where('code', 'A1')->value('name'));
        $this->assertSame('Teclados', Product::where('code', 'B2')->first()->category->name);
        $this->assertFalse((bool) Product::where('code', 'OLD')->value('active'));
    }

    public function test_name_category_description_and_specs_of_existing_products_are_kept(): void
    {
        $category = \App\Models\Category::create(['name' => 'Mouses', 'slug' => 'mouses']);
        Product::create(['code' => 'A1', 'name' => 'Mouse corregido', 'price' => 1000, 'category_id' => $category->id,
            'description' => 'Texto trabajado', 'specs' => [['DPI', '3200']]]);

        $file = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($file, "code,name,category,price,description\nA1,MOUSE MAL,Redes,1500,Texto del proveedor\nB2,Teclado,Teclados,3000,Teclado nuevo\n");
        (new ProductCsvImporter())->import($file);

        $a1 = Product::where('code', 'A1')->first();
        $this->assertEquals(1500, $a1->price);
        $this->assertSame(['Mouse corregido', $category->id], [$a1->name, $a1->category_id]);
        $this->assertSame('Texto trabajado', $a1->description);
        $this->assertSame([['DPI', '3200']], Product::where('code', 'A1')->first()->specs);
        $this->assertSame('Teclado nuevo', Product::where('code', 'B2')->value('description'));
    }
}
