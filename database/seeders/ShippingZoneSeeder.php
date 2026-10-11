<?php

namespace Database\Seeders;

use App\Models\ShippingZone;
use Illuminate\Database\Seeder;

/**
 * Zonas iniciales. LOS PRECIOS SON ESTIMATIVOS: ajustarlos en /admin/zonas-envio según el correo o cadete que se use.
 * Los rangos son de código postal (4 dígitos). La zona sin rangos funciona como "resto del país".
 */
class ShippingZoneSeeder extends Seeder
{
    public function run(): void
    {
        $zones = [
            ['name' => 'Retiro sin cargo', 'name_en' => 'Free pickup', 'eta' => 'Coordinamos día y horario por WhatsApp', 'eta_en' => 'We arrange day and time via WhatsApp',
                'is_pickup' => true, 'price' => 0, 'postal_ranges' => null],
            ['name' => 'CABA', 'name_en' => 'Buenos Aires City', 'eta' => '24 a 48 h hábiles', 'eta_en' => '1–2 business days',
                'postal_ranges' => "1000-1499", 'price' => 7500, 'free_from' => 400000],
            ['name' => 'GBA', 'name_en' => 'Greater Buenos Aires', 'eta' => '48 a 72 h hábiles', 'eta_en' => '2–3 business days',
                'postal_ranges' => "1500-1999", 'price' => 10500, 'free_from' => 600000],
            ['name' => 'Provincia de Buenos Aires', 'name_en' => 'Buenos Aires Province', 'eta' => '3 a 5 días hábiles', 'eta_en' => '3–5 business days',
                'postal_ranges' => "2700-2999\n6000-6799\n7000-8199", 'price' => 15000],
            ['name' => 'Resto del país', 'name_en' => 'Rest of Argentina', 'eta' => '4 a 7 días hábiles', 'eta_en' => '4–7 business days',
                'postal_ranges' => null, 'price' => 19000],
        ];

        foreach ($zones as $i => $zone) {
            ShippingZone::updateOrCreate(['name' => $zone['name']], $zone + ['sort' => $i, 'active' => true]);
        }
    }
}
