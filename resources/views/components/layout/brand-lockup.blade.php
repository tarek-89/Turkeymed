@props([
    'dark' => false,      // true on dark surfaces (footer, mobile drawer) → white-text logo
    'height' => 'h-10',   // Tailwind height utility for the logo image
])
@php
    $file = $dark ? 'images/logo-white.png' : 'images/logo.png';
    $hasLogo = is_file(public_path($file));
@endphp
@if ($hasLogo)
    <img
        src="{{ asset($file) }}"
        alt="{{ config('site.brand') }}"
        width="512"
        height="170"
        class="{{ $height }} w-auto"
    >
@else
    {{-- Fallback to the SVG mark + wordmark until the logo files are added. --}}
    <x-layout.brand-mark />
    <span>{{ config('site.brand') }}</span>
@endif
