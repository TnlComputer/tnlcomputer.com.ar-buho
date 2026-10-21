<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Cartel de reseñas · TNL Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @page { size: A4; margin: 0; }
        body { background: #e4e4e7; color: #18181b; }
        .sheet { width: 210mm; min-height: 297mm; margin: 10mm auto; background: #fff; box-shadow: 0 4px 24px rgb(0 0 0 / .12); }
        .qr svg { width: 100%; height: auto; display: block; }
        .card-grid { display: grid; grid-template-columns: repeat(2, 1fr); grid-template-rows: repeat(4, 1fr); height: 297mm; }
        .card-grid > div { border: 1px dashed #d4d4d8; }
        @media print {
            body { background: #fff; }
            .sheet { margin: 0; box-shadow: none; page-break-after: always; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print mx-auto flex max-w-[210mm] flex-wrap items-center justify-between gap-3 px-4 pt-6">
        <p class="text-sm text-zinc-600">Hoja 1: cartel A4 para el mostrador o la vidriera. Hoja 2: 8 tarjetas para recortar y entregar con cada compra.</p>
        <button type="button" data-print class="btn btn-primary">Imprimir</button>
    </div>

    {{-- Cartel A4 --}}
    <section class="sheet flex flex-col items-center justify-center px-[20mm] text-center">
        <img src="{{ asset('logo512.png') }}" alt="" class="h-[28mm] w-[28mm]">
        <p class="mt-6 font-display text-[16pt] font-bold tracking-wide text-[#a8338f] uppercase">TNL Computer</p>
        <h1 class="mt-4 font-display text-[34pt] leading-tight font-bold">¿Te gustó cómo te atendimos?</h1>
        <p class="mt-4 text-[17pt] text-zinc-600">Escaneá el código y dejanos tu opinión en Google.<br>Nos ayuda muchísimo y le sirve a otros clientes.</p>
        <div class="qr mt-10 w-[95mm] rounded-2xl border-4 border-[#a8338f] p-4">{!! $qr !!}</div>
        <p class="mt-6 flex items-center gap-2 text-[28pt] text-amber-500" aria-hidden="true">★★★★★</p>
        <p class="mt-6 text-[12pt] text-zinc-500">Abrí la cámara del celular y apuntá al código</p>
        <p class="mt-1 font-mono text-[10pt] text-zinc-400">{{ $url }}</p>
        <p class="mt-10 text-[15pt] font-semibold">¡Gracias por elegirnos!</p>
    </section>

    {{-- 8 tarjetas para recortar --}}
    <section class="sheet card-grid">
        @for ($i = 0; $i < 8; $i++)
            <div class="flex items-center gap-[5mm] px-[7mm]">
                <div class="qr w-[38mm] shrink-0">{!! $qr !!}</div>
                <div>
                    <p class="font-display text-[10pt] font-bold text-[#a8338f] uppercase">TNL Computer</p>
                    <p class="mt-1 font-display text-[14pt] leading-tight font-bold">¿Cómo te fue con tu compra?</p>
                    <p class="mt-2 text-[9.5pt] leading-snug text-zinc-600">Escaneá y dejanos tu reseña en Google. ¡Gracias!</p>
                    <p class="mt-2 text-[14pt] text-amber-500" aria-hidden="true">★★★★★</p>
                </div>
            </div>
        @endfor
    </section>
</body>
</html>
