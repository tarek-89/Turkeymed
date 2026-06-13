@props([
    'level' => 'h2',
    'size' => null,
])

@php
    // Visual size defaults to the semantic level but can be decoupled
    // (e.g. an <h1> that looks like the display size).
    $size ??= $level;

    $scale = [
        'display' => 'text-[clamp(2.4rem,6vw,3.75rem)] leading-[1.05] tracking-[-0.035em]',
        'h1' => 'text-[clamp(2rem,5vw,3rem)] leading-[1.08] tracking-[-0.03em]',
        'h2' => 'text-[clamp(1.7rem,4vw,2.25rem)] leading-[1.1] tracking-[-0.025em]',
        'h3' => 'text-[clamp(1.25rem,2.4vw,1.5rem)] leading-[1.15] tracking-[-0.02em]',
        'h4' => 'text-[clamp(1.1rem,2vw,1.375rem)] leading-[1.25] tracking-[-0.01em]',
    ];

    $classes = 'font-extrabold text-ink '.($scale[$size] ?? $scale['h2']);
@endphp

<{{ $level }} {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</{{ $level }}>
