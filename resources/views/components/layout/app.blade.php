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
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $direction }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    @if ($noindex)
        <meta name="robots" content="noindex">
    @endif
    <link rel="canonical" href="{{ $canonicalUrl }}">

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
    @if ($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">

    {{-- Aurora Design System fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="flex min-h-screen flex-col bg-surface text-ink antialiased {{ $bodyClass }}">
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
