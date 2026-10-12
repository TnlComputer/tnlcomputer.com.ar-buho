<?php

namespace Database\Seeders;

use App\Models\Certification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Certificaciones del portfolio publicado (assets/certs/certs.json del hosting).
 * Los PDF/PNG siguen en public/assets/certs/: copiarlos desde el public_html actual (ver DEPLOY.md).
 */
class CertificationSeeder extends Seeder
{
    // Mismo criterio que usaba el portfolio publicado para elegir destacados.
    private const FEATURED_KEYWORDS = [
        'spring boot', 'spring framework', 'mysql', 'oracle', 'inteligencia artificial', 'chatgpt',
        'laravel', 'python', 'javascript', 'ciberseguridad', 'business', 'github',
    ];

    public function run(): void
    {
        $certs = json_decode(file_get_contents(database_path('data/certs.json')), true);

        foreach ($certs as $cert) {
            $title = trim($cert['title']);
            Certification::updateOrCreate(
                // Se identifica por archivo: así cambiar el título no crea un certificado duplicado.
                isset($cert['files'][0]['file']) ? ['file' => $cert['files'][0]['file']] : ['title' => $title, 'issuer' => $cert['issuer']],
                [
                    'title' => $title,
                    'issuer' => $cert['issuer'],
                    // "2023" o "2023-10" se completan a una fecha válida (si no, se guardaría como 1970).
                    'issued_at' => match (true) {
                        (bool) preg_match('/^\d{4}$/', (string) ($cert['date'] ?? '')) => $cert['date'].'-01-01',
                        (bool) preg_match('/^\d{4}-\d{2}$/', (string) ($cert['date'] ?? '')) => $cert['date'].'-01',
                        default => $cert['date'] ?? null,
                    },
                    'file' => $cert['files'][0]['file'] ?? null,
                    'featured' => Str::contains(Str::lower($title), self::FEATURED_KEYWORDS),
                ],
            );
        }
    }
}
