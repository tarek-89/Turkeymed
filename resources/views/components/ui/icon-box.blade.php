@props([
    'variant' => 'default', // default | light (on dark) | gradient
    'size' => 'md',
])

@php
    $sizes = [
        'md' => 'h-[46px] w-[46px] rounded-md',
        'lg' => 'h-14 w-14 rounded-md',
    ];

    $variants = [
        'default' => 'bg-cyan-50',
        'light' => 'bg-white/15',
        'gradient' => 'bg-[linear-gradient(135deg,var(--color-navy-700),var(--color-cyan-400))]',
    ];

    $classes = 'grid flex-none place-items-center '.($sizes[$size] ?? $sizes['md']).' '.($variants[$variant] ?? $variants['default']);
@endphp

<span {{ $attributes->merge(['class' => $classes, 'aria-hidden' => 'true']) }}>{{ $slot }}</span>
