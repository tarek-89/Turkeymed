@props([
    'title' => null,
    'description' => null,
    'canonical' => null,
    'ogType' => 'website',
    'ogImage' => null,
    'alternates' => null,
    'noindex' => false,
    'bodyClass' => '',
    'fab' => true, // set false on pages that render their own sticky CTA (e.g. services)
])
@php
    $locale = app()->getLocale();
    $direction = \App\Support\Locale::direction($locale);
    $pageTitle = $title ?? __('meta.default_title');
    $pageDescription = $description ?? __('meta.default_description');
    $canonicalUrl = $canonical ?? url()->current();
    $ogImageUrl = $ogImage ?? \App\Support\Branding::ogImageUrl();
    $ogLocaleMap = ['en' => 'en_US', 'fr' => 'fr_FR', 'es' => 'es_ES', 'ar' => 'ar_AR'];
    $ogLocale = $ogLocaleMap[$locale] ?? $locale;
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $direction }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @if (config('site.gtm_id'))
        {{-- Google Tag Manager — deferred off the critical path.
             dataLayer is created immediately (cheap) so events can queue, but
             the heavy gtm.js is only fetched on the first user interaction, or
             on idle / a short timeout fallback so no-interaction visits are
             still measured. This keeps GTM from competing with first paint. --}}
        <script>
            window.dataLayer = window.dataLayer || [];
            (function (w, d, id) {
                var loaded = false;
                var events = ['scroll', 'keydown', 'pointerdown', 'touchstart', 'mousemove'];
                function loadGTM() {
                    if (loaded) { return; }
                    loaded = true;
                    events.forEach(function (e) { w.removeEventListener(e, loadGTM); });
                    w.dataLayer.push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });
                    var f = d.getElementsByTagName('script')[0];
                    var j = d.createElement('script');
                    j.async = true;
                    j.src = 'https://www.googletagmanager.com/gtm.js?id=' + id;
                    f.parentNode.insertBefore(j, f);
                }
                events.forEach(function (e) { w.addEventListener(e, loadGTM, { passive: true, once: true }); });
                if ('requestIdleCallback' in w) {
                    w.requestIdleCallback(loadGTM, { timeout: 5000 });
                } else {
                    w.setTimeout(loadGTM, 4000);
                }
            })(window, document, '{{ config('site.gtm_id') }}');
        </script>
    @endif

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    @if ($noindex || ! config('site.indexable', true))
        <meta name="robots" content="noindex, nofollow">
    @endif
    @if (config('site.google_site_verification'))
        <meta name="google-site-verification" content="{{ config('site.google_site_verification') }}">
    @endif
    @if (config('site.bing_site_verification'))
        <meta name="msvalidate.01" content="{{ config('site.bing_site_verification') }}">
    @endif
    <link rel="canonical" href="{{ $canonicalUrl }}">

    {{-- Icons & PWA --}}
    <link rel="icon" href="/favicon.ico?v=2" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png?v=2">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png?v=2">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png?v=2">
    <link rel="manifest" href="/site.webmanifest">
    <meta name="theme-color" content="#1C4068">
    <link rel="alternate" type="application/rss+xml" title="{{ config('site.brand') }}" href="{{ url('/feed.xml') }}">

    {{-- hreflang alternates (when a page provides translations) --}}
    @if ($alternates)
        @foreach ($alternates as $code => $href)
            <link rel="alternate" hreflang="{{ $code }}" href="{{ $href }}">
        @endforeach
        <link rel="alternate" hreflang="x-default" href="{{ $alternates['en'] ?? array_values($alternates)[0] }}">
    @endif

    {{-- Open Graph / Twitter --}}
    <meta property="og:site_name" content="{{ config('site.brand') }}">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $ogImageUrl }}">
    <meta property="og:locale" content="{{ $ogLocale }}">
    @if ($alternates)
        @foreach ($alternates as $code => $href)
            @if ($code !== $locale)
                <meta property="og:locale:alternate" content="{{ $ogLocaleMap[$code] ?? $code }}">
            @endif
        @endforeach
    @endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $ogImageUrl }}">

    {{-- Sitewide entity graph: MedicalOrganization + WebSite --}}
    <x-seo.site-schema />

    {{-- Aurora Design System fonts are self-hosted (Fontsource) and bundled via Vite. --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="flex min-h-screen flex-col bg-surface text-ink antialiased {{ $bodyClass }}">
    @if (config('site.gtm_id'))
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ config('site.gtm_id') }}" height="0" width="0" style="display:none;visibility:hidden" title="Google Tag Manager"></iframe></noscript>
    @endif

    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:start-0 focus:top-0 focus:z-[200] focus:rounded-br-[10px] focus:bg-navy-700 focus:px-4 focus:py-2.5 focus:text-white">
        {{ __('common.skip_to_content') }}
    </a>

    <x-layout.header :alternates="$alternates" />
    <x-layout.mobile-drawer />

    <main id="main" class="flex-1">
        {{ $slot }}
    </main>

    <x-layout.footer />
    @if ($fab)
        <x-layout.whatsapp-fab />
    @endif

    @stack('scripts')
</body>
</html>
