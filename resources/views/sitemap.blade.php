{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
@foreach ($urls as [$es, $en])
@foreach ([$es, $en] as $loc)
    <url>
        <loc>{{ $loc }}</loc>
        <xhtml:link rel="alternate" hreflang="es" href="{{ $es }}"/>
        <xhtml:link rel="alternate" hreflang="en" href="{{ $en }}"/>
    </url>
@endforeach
@endforeach
</urlset>
