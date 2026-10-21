<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">
<channel>
    <title>TNL Computer</title>
    <link>{{ route('shop.index') }}</link>
    <description>{{ config('tnl.tagline') }}</description>
@foreach ($products as $product)
    <item>
        <g:id>{{ $product->code }}</g:id>
        <g:title>{{ \Illuminate\Support\Str::limit(preg_replace('/^\[[^\]]*\]\s*/', '', $product->name), 150, '') }}</g:title>
        <g:description>{{ \Illuminate\Support\Str::limit(strip_tags($product->description ?: $product->name), 5000, '') }}</g:description>
        <g:link>{{ route('shop.show', $product) }}</g:link>
        <g:image_link>{{ $product->imageUrl() }}?v={{ $imageVersion }}</g:image_link>
        <g:availability>in_stock</g:availability>
        <g:price>{{ number_format((float) $product->price, 2, '.', '') }} ARS</g:price>
        <g:condition>new</g:condition>
@php $ids = \App\Support\ProductIdentifiers::for($product); @endphp
@if ($ids['brand'])
        <g:brand>{{ $ids['brand'] }}</g:brand>
@endif
@if ($ids['gtin'])
        <g:gtin>{{ $ids['gtin'] }}</g:gtin>
@endif
@if ($ids['mpn'])
        <g:mpn>{{ $ids['mpn'] }}</g:mpn>
@endif
@unless (\App\Support\ProductIdentifiers::identified($ids))
        <g:identifier_exists>no</g:identifier_exists>
@endunless
@foreach ($product->specs ?? [] as [$label, $value])
        <g:product_detail>
            <g:section_name>Características</g:section_name>
            <g:attribute_name>{{ $label }}</g:attribute_name>
            <g:attribute_value>{{ $value }}</g:attribute_value>
        </g:product_detail>
@endforeach
@if ($product->category)
        <g:product_type>{{ $product->category->name }}</g:product_type>
@endif
    </item>
@endforeach
</channel>
</rss>
