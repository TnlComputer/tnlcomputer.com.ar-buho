<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Support\ProductIdentifiers;
use PHPUnit\Framework\TestCase;

class ProductIdentifiersTest extends TestCase
{
    private function ids(string $code, string $name): array
    {
        return ProductIdentifiers::for(new Product(['code' => $code, 'name' => $name]));
    }

    public function test_part_number_in_brackets_or_trailing_parentheses(): void
    {
        $this->assertSame(['brand' => 'Kingston', 'mpn' => 'KF552C40BBA-16', 'gtin' => null],
            $this->ids('KF552C40BBA-16-MEMORIA-PC-FURY-DDR5-16GB', '[KF552C40BBA-16] Memoria PC Fury DDR5 16GB 5200 Beast RGB Negra'));
        $this->assertSame(['brand' => 'Kingston', 'mpn' => 'SA400S37/480G', 'gtin' => null],
            $this->ids('19648', 'Disco Solido SSD 480GB SATA KINGSTON A400 (SA400S37/480G)'));
        $this->assertSame('GV-N506TEAGLEOC ICE-8GD',
            $this->ids('WHITE', '[GV-N506TEAGLEOC ICE-8GD] PLACA DE VIDEO GIGABYTE RTX 5060TI EAGLE OC ICE 8GB (WHITE)')['mpn']);
    }

    public function test_barcode_needs_a_valid_check_digit(): void
    {
        $ids = $this->ids('25830', 'Microfono REDRAGON MICROFONO BLAZAR BLACK (GM300) (6950376776402)');
        $this->assertSame(['brand' => 'Redragon', 'mpn' => 'GM300', 'gtin' => '6950376776402'], $ids);

        $this->assertNull($this->ids('1', 'Mouse Genius (6950376776403)')['gtin']);
    }

    public function test_own_code_is_used_only_when_it_looks_like_a_part_number(): void
    {
        $this->assertSame('WD102PURP', $this->ids('WD102PURP', 'Disco Interno HDD WESTERN DIGITAL Purple PRO 10TB')['mpn']);
        $this->assertNull($this->ids('41947', 'Webcam Genius Facecam Light Black')['mpn']);
        $this->assertNull($this->ids('TN42085', 'PLACA DE VIDEO ASUS PRIME RADEON RX 9070 XT 16GB')['mpn']);
        // Lo que va entre paréntesis y no es un número de parte se ignora.
        $this->assertNull($this->ids('41851', 'MOTHER MSI A520M-A PRO AM4 DDR4 (BULK)')['mpn']);
        $this->assertNull($this->ids('1', 'Procesador Intel Core i5 (LGA1700)')['mpn']);
    }

    public function test_brand_is_the_first_one_named_and_alternatives_have_none(): void
    {
        $this->assertSame('ASUS', $this->ids('1', 'PLACA DE VIDEO ASUS PRIME RADEON RX 9070 XT')['brand']);
        $this->assertSame('Lenovo', $this->ids('1', 'Notebook Lenovo ThinkBook 16 G8 Intel Core 7')['brand']);
        $this->assertSame('Intel', $this->ids('1', 'Procesador Core i5-14400F 2.5GHz')['brand']);
        $this->assertNull($this->ids('1', 'Toner Samsung alternativo Veryjet MLT-D111S (ALTMLT111)')['brand']);
        $this->assertNull($this->ids('1', 'CARGADOR DE NOTEBOOK GENERICO HP 90W')['brand']);
        // "CM" es Cooler Master solo como palabra suelta, no dentro de "5CM".
        $this->assertNull($this->ids('1', 'Ventilador 12CM negro')['brand']);
    }

    public function test_identified_needs_gtin_or_brand_and_part_number(): void
    {
        $this->assertTrue(ProductIdentifiers::identified(['brand' => null, 'mpn' => null, 'gtin' => '6950376776402']));
        $this->assertTrue(ProductIdentifiers::identified(['brand' => 'HP', 'mpn' => '1F3Y2A', 'gtin' => null]));
        $this->assertFalse(ProductIdentifiers::identified(['brand' => 'HP', 'mpn' => null, 'gtin' => null]));
        $this->assertFalse(ProductIdentifiers::identified(['brand' => null, 'mpn' => 'ALTMLT111', 'gtin' => null]));
    }
}
