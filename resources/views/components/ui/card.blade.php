@props([
    'variant' => 'default', // default | gradient | soft
    'href' => null,
    'interactive' => false,
    'as' => 'div',
])

@php
    $variants = [
        'default' => 'bg-white border border-line',
        'gradient' => 'bg-[linear-gradient(160deg,var(--color-navy-700),var(--color-cyan-700))] text-white border border-transparent',
        'soft' => 'bg-cyan-50 border border-cyan-100',
    ];

    $classes = collect([
        'rounded-xl p-6 transition duration-150',
        $variants[$variant] ?? $variants['default'],
        $interactive ? 'hover:-translate-y-1 hover:shadow-lg' : '',
    ])->filter()->implode(' ');

    $tag = $href ? 'a' : $as;
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</{{ $tag }}>
