@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
    'block' => false,
])

@php
    $base = 'inline-flex items-center justify-center gap-2.5 rounded-md font-bold text-center transition duration-150';

    $sizes = [
        'md' => 'min-h-[48px] px-6 py-3.5 text-base',
        'sm' => 'min-h-[42px] px-4 py-2.5 text-sm',
    ];

    $variants = [
        'primary' => 'text-white bg-[linear-gradient(135deg,var(--color-navy-700),var(--color-cyan-600))] shadow-[0_14px_30px_-16px_rgba(28,64,104,0.7)] hover:-translate-y-0.5',
        'accent' => 'text-navy-900 bg-cyan-400 shadow-glow hover:-translate-y-0.5',
        'secondary' => 'bg-white text-navy-700 border border-n-300 hover:border-navy-700',
        'ghost' => 'bg-transparent text-cyan-700 hover:bg-cyan-50',
        'translucent' => 'bg-white/10 text-white border border-white/25 hover:bg-white/20',
    ];

    $classes = collect([
        $base,
        $sizes[$size] ?? $sizes['md'],
        $variants[$variant] ?? $variants['primary'],
        $block ? 'w-full' : '',
    ])->filter()->implode(' ');
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
