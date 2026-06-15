@props([
    'dark' => false,      // true on dark surfaces (footer, mobile drawer) → white-text logo
    'height' => 'h-10',   // Tailwind height utility for the logo image
])
@php
    // Intrinsic dimensions must match each file's real pixel ratio, or Lighthouse
    // flags "incorrect aspect ratio". logo.png is 500×200, logo-white.png 384×154.
    [$file, $logoWidth, $logoHeight] = $dark
        ? ['images/logo-white.png', 384, 154]
        : ['images/logo.png', 500, 200];
    $hasLogo = is_file(public_path($file));
@endphp
@if ($hasLogo)
    <img
        src="{{ asset($file) }}"
        alt="{{ config('site.brand') }}"
        width="{{ $logoWidth }}"
        height="{{ $logoHeight }}"
        class="{{ $height }} w-auto"
    >
@else
    {{-- Fallback to the SVG mark + wordmark until the logo files are added. --}}
    <x-layout.brand-mark />
    <span>{{ config('site.brand') }}</span>
@endif
