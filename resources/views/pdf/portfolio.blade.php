{{-- Portfolio para descargar (dompdf: CSS 2.1, sin flex ni grid; maquetado con tablas). --}}
@php $p = config('tnl.profile'); @endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ $p['name'] }} · {{ $p['role'] }}</title>
    <style>
        @page { margin: 16mm 15mm 18mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9.5pt; line-height: 1.45; color: #27272a; }
        a { color: #a8338f; text-decoration: none; }
        h1 { margin: 0; font-size: 22pt; color: #18181b; }
        h2 { margin: 22px 0 10px; padding-bottom: 4px; border-bottom: 2px solid #a8338f; font-size: 13pt; color: #18181b; }
        h3 { margin: 0; font-size: 10.5pt; color: #18181b; }
        p { margin: 0 0 6px; }
        .eyebrow { font-size: 8pt; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; color: #a8338f; }
        .muted { color: #71717a; }
        .small { font-size: 8pt; }
        .chip { display: inline-block; margin: 0 3px 4px 0; padding: 1px 7px; border-radius: 8px; background: #f4f4f5; font-size: 8pt; color: #3f3f46; }
        .chip-main { background: #a8338f; color: #fff; }
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; }
        .project { padding: 8px 0; border-bottom: 1px solid #e4e4e7; page-break-inside: avoid; }
        .certs td { padding: 3px 6px 3px 0; border-bottom: 1px solid #f4f4f5; font-size: 8.5pt; }
        footer { position: fixed; bottom: -10mm; left: 0; right: 0; font-size: 7.5pt; color: #a1a1aa; text-align: center; }
    </style>
</head>
<body>
    <footer>{{ $p['name'] }} · {{ $p['email'] }} · {{ lroute('portfolio') }}</footer>

    <table>
        <tr>
            <td>
                <p class="eyebrow">{{ $p['name'] }}</p>
                <h1>{{ $p['role'] }}</h1>
                <p style="margin-top: 8px; font-size: 11pt; color: #52525b;">{{ __($p['lead']) }}</p>
                <p class="small" style="margin-top: 10px;">
                    <a href="mailto:{{ $p['email'] }}">{{ $p['email'] }}</a><br>
                    LinkedIn: <a href="{{ $p['linkedin'] }}">{{ preg_replace('#^https?://(www\.)?#', '', $p['linkedin']) }}</a><br>
                    GitHub: <a href="{{ $p['github'] }}">{{ preg_replace('#^https?://(www\.)?#', '', $p['github']) }}</a><br>
                    Web: <a href="{{ lroute('portfolio') }}">{{ preg_replace('#^https?://(www\.)?#', '', lroute('portfolio')) }}</a>
                </p>
            </td>
            <td style="width: 34mm; text-align: right;">
                <img src="{{ public_path($p['photo']) }}" alt="" style="width: 32mm; border-radius: 6px;">
            </td>
        </tr>
    </table>

    <h2>{{ __('Sobre mí') }}</h2>
    <h3>{{ __($p['about_title']) }}</h3>
    <div style="margin-top: 6px;">
        @foreach ($p['about'] as $paragraph)<p>{{ __($paragraph) }}</p>@endforeach
    </div>
    @foreach ($skills as $group => $items)
        <p style="margin-top: 8px;"><span class="eyebrow" style="color: #71717a;">{{ __($group) }}</span><br>
            @foreach ($items as $skill)<span @class(['chip', 'chip-main' => $loop->parent->first])>{{ __($skill->name) }}</span>@endforeach
        </p>
    @endforeach

    <h2>{{ __('Experiencia') }}</h2>
    @foreach ($p['experience'] as [$period, $role, $text])
        <table style="margin-bottom: 8px; page-break-inside: avoid;">
            <tr>
                <td style="width: 32mm;" class="small"><strong style="color: #a8338f;">{{ __($period) }}</strong></td>
                <td><h3>{{ __($role) }}</h3><p class="muted">{{ __($text) }}</p></td>
            </tr>
        </table>
    @endforeach

    <h2>{{ __('Proyectos') }}</h2>
    @foreach ($projects as $project)
        <div class="project">
            <p class="small muted" style="margin: 0;">
                @if ($project->kind)<strong style="color: #a8338f; text-transform: uppercase;">{{ $project->tr('kind') }}</strong>@endif
                @if ($project->year) · {{ $project->year }}@endif
            </p>
            <h3>{{ $project->tr('title') }}</h3>
            <p style="margin-top: 3px;">{{ $project->tr('description') }}</p>
            @if ($project->tags)
                <p style="margin: 0;">@foreach ($project->tags as $tag)<span class="chip">{{ $tag }}</span>@endforeach</p>
            @endif
            @if ($project->demo_url || $project->code_url)
                <p class="small" style="margin: 0;">
                    @if ($project->demo_url){{ __('Ver sitio') }}: <a href="{{ $project->demo_url }}">{{ preg_replace('#^https?://(www\.)?#', '', $project->demo_url) }}</a>@endif
                    @if ($project->demo_url && $project->code_url) &nbsp;·&nbsp; @endif
                    @if ($project->code_url){{ __('Código') }}: <a href="{{ $project->code_url }}">{{ preg_replace('#^https?://(www\.)?#', '', $project->code_url) }}</a>@endif
                </p>
            @endif
        </div>
    @endforeach

    @if ($certifications->isNotEmpty())
        <h2>{{ __('Certificaciones') }}</h2>
        <p class="muted small">
            {{ trans_choice(':count certificado|:count certificados', $certifications->count()) }}
            @if (is_en()) (titles shown as issued, in Spanish)@endif
        </p>
        <table class="certs">
            @foreach ($certifications as $cert)
                <tr>
                    <td style="width: 16mm;" class="muted">{{ $cert->issued_at?->format('m/Y') }}</td>
                    <td lang="es">@if ($cert->fileUrl())<a href="{{ $cert->fileUrl() }}" style="color: #27272a;">{{ $cert->title }}</a>@else{{ $cert->title }}@endif</td>
                    <td style="width: 38mm;" class="muted">{{ $cert->issuer }}</td>
                </tr>
            @endforeach
        </table>
    @endif
</body>
</html>
