@props([
    'href' => '#',
    'variant' => 'default', // default (teal on light) | light (white on dark)
])

@php
    $color = $variant === 'light' ? 'text-white' : 'text-cyan-800';
    $classes = 'inline-flex items-center gap-1.5 font-bold '.$color;
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="rtl:-scale-x-100">
        <path d="M5 12h14M13 6l6 6-6 6" />
    </svg>
</a>
