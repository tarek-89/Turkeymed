{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>{{ config('site.brand') }}</title>
        <link>{{ url('/blog') }}</link>
        <description>{{ __('home.journal_title') }}</description>
        <language>{{ \App\Support\Locale::DEFAULT }}</language>
        <atom:link href="{{ url('/feed.xml') }}" rel="self" type="application/rss+xml"/>
@foreach ($posts as $post)
        <item>
            <title>{{ $post->title }}</title>
            <link>{{ $post->url() }}</link>
            <guid isPermaLink="true">{{ $post->url() }}</guid>
@if ($post->published_at)
            <pubDate>{{ $post->published_at->toRssString() }}</pubDate>
@endif
@if ($post->metaDescription())
            <description>{{ $post->metaDescription() }}</description>
@endif
        </item>
@endforeach
    </channel>
</rss>
