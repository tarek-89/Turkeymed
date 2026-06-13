@props([
    'value' => null,
    'label' => null,
    'accent' => null, // optional emphasised suffix, e.g. "k+" or "%"
    'variant' => 'default', // default (navy on light) | light (white on dark surfaces)
])

@php
    $valueColor = $variant === 'light' ? 'text-white' : 'text-navy-700';
    $accentColor = $variant === 'light' ? 'text-cyan-200' : 'text-cyan-600';
    $labelColor = $variant === 'light' ? 'text-white/75' : 'text-muted';
@endphp

<div {{ $attributes->merge(['class' => 'text-center']) }}>
    <div class="text-[clamp(2rem,5vw,2.75rem)] font-extrabold leading-none tracking-tight {{ $valueColor }}">
        {{ $value ?? $slot }}@if ($accent)<span class="{{ $accentColor }}">{{ $accent }}</span>@endif
    </div>
    @if ($label)
        <div class="mt-2 text-sm {{ $labelColor }}">{{ $label }}</div>
    @endif
</div>
