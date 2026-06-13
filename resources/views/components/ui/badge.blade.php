@props([
    'variant' => 'soft', // soft | solid | accent | outline | translucent (on dark)
    'dot' => false,
])

@php
    $variants = [
        'soft' => 'bg-cyan-50 text-cyan-800',
        'solid' => 'bg-[linear-gradient(135deg,var(--color-navy-700),var(--color-cyan-600))] text-white',
        'accent' => 'bg-cyan-400 text-navy-900',
        'outline' => 'border border-line bg-white text-muted',
        'translucent' => 'border border-white/20 bg-white/10 text-cyan-100',
    ];

    $classes = 'inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-bold '.($variants[$variant] ?? $variants['soft']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if ($dot)
        <span class="h-1.5 w-1.5 flex-none rounded-full bg-current" aria-hidden="true"></span>
    @endif
    {{ $slot }}
</span>
