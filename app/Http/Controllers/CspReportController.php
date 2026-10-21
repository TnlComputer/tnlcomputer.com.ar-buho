<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Reportes de la Content-Security-Policy que mandan los navegadores: qué recurso se bloqueó (o se bloquearía,
 * en modo "report") y en qué página. Sirve para ver que nada legítimo quede afuera antes de pasar a "enforce".
 */
class CspReportController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $report = json_decode($request->getContent(), true)['csp-report'] ?? null;

        if (is_array($report)) {
            Log::warning('CSP', array_map(fn ($v) => Str::limit((string) $v, 300), array_intersect_key($report, array_flip([
                'document-uri', 'violated-directive', 'effective-directive', 'blocked-uri', 'source-file', 'line-number',
            ]))));
        }

        return response()->noContent();
    }
}
