{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
@foreach ($urls as $url)
    <url>
        <loc>{{ $url['loc'] }}</loc>
@if (! empty($url['lastmod']))
        <lastmod>{{ $url['lastmod'] }}</lastmod>
@endif
@foreach ($url['alternates'] as $code => $href)
        <xhtml:link rel="alternate" hreflang="{{ $code }}" href="{{ $href }}"/>
@endforeach
@if (! empty($url['image']))
        <image:image><image:loc>{{ $url['image'] }}</image:loc></image:image>
@endif
    </url>
@endforeach
</urlset>
