<?php

namespace App\Support;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

/**
 * Filtros anti-spam de los formularios públicos (sin servicios externos):
 * - Honeypot: campo oculto "website" que una persona nunca completa.
 * - Tiempo mínimo: el formulario lleva la hora en que se cargó, cifrada para que no se pueda falsificar.
 *   Un bot lo envía al instante (o sin cargar la página); una persona tarda al menos unos segundos.
 */
class FormGuard
{
    public const FIELD = '_started';

    /** Valor del campo oculto: la hora actual cifrada. */
    public static function stamp(): string
    {
        return Crypt::encryptString((string) now()->timestamp);
    }

    /** Envíos que se descartan en silencio: honeypot completo o formulario que no salió de nuestra página. */
    public static function isBot(Request $request): bool
    {
        if ($request->filled('website')) {
            return true;
        }

        return self::enabled() && self::startedAt($request) === null;
    }

    /** Envío más rápido de lo que tarda una persona: se le pide que lo intente de nuevo. */
    public static function tooFast(Request $request): bool
    {
        if (! self::enabled()) {
            return false;
        }

        $started = self::startedAt($request);

        return $started !== null && now()->timestamp - $started < config('tnl.form_min_seconds');
    }

    private static function enabled(): bool
    {
        return config('tnl.form_min_seconds') > 0;
    }

    private static function startedAt(Request $request): ?int
    {
        try {
            $value = Crypt::decryptString((string) $request->input(self::FIELD));
        } catch (DecryptException) {
            return null;
        }

        return ctype_digit($value) ? (int) $value : null;
    }
}
