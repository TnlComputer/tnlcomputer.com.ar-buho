<?php

namespace Database\Seeders;

use App\Models\HostingPlan;
use Illuminate\Database\Seeder;

class HostingPlanSeeder extends Seeder
{
    /**
     * Precios de referencia (octubre 2026), relevados contra 11 proveedores argentinos (Hostinger, DonWeb, Neolo,
     * WNPower, DylanHost, OpenHosting, Tecnoweb, Latincloud, Rackvirtual, Webhosting Argentina, Baehost).
     * Los montos de abajo son FINALES con IVA; como TNL publica "+ IVA" se guardan netos (÷ 1,21, redondeado a $100).
     * Se ajustan desde /admin/hosting.
     */
    public function run(): void
    {
        $plans = [
            // nombre, dominios, disco, transferencia, [DA: mensual, semestral, anual, bianual], [cPanel: ...] (por mes)
            ['Novice', 1, '7 GB SSD', '5 GB', [6900, 6200, 5500, 4900], [8400, 7500, 6700, 5900]],
            ['Advance', 1, '10 GB SSD', '10 GB', [8900, 7900, 7100, 6300], [10400, 9300, 8300, 7400]],
            ['Oro', 1, '10 GB SSD', '20 GB', [10900, 9800, 8700, 7700], [12400, 11100, 9900, 8800]],
            ['Platino', 1, '15 GB SSD', '30 GB', [13900, 12500, 11100, 9900], [15900, 14300, 12700, 11300]],
        ];

        $net = fn (int $final) => round($final / 1.21, -2);

        foreach ($plans as $i => [$name, $domains, $disk, $transfer, $da, $cp]) {
            foreach (['directadmin' => $da, 'cpanel' => $cp] as $panel => $prices) {
                HostingPlan::updateOrCreate(
                    ['name' => $name, 'panel' => $panel],
                    [
                        'domains' => $domains, 'disk' => $disk, 'transfer' => $transfer,
                        'price_monthly' => $net($prices[0]), 'price_semester' => $net($prices[1]),
                        'price_annual' => $net($prices[2]), 'price_biannual' => $net($prices[3]),
                        'highlighted' => $name === 'Advance', 'sort' => $i,
                    ],
                );
            }
        }
    }
}
