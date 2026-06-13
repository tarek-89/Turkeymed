@props([
    'variant' => 'secondary', // any x-ui.button variant; use 'translucent' on dark/gradient surfaces
    'size' => 'md',
    'block' => false,
    'label' => null,
])

@php
    $number = preg_replace('/\D/', '', (string) config('site.whatsapp'));
@endphp

<x-ui.button
    :href="'https://wa.me/'.$number"
    :variant="$variant"
    :size="$size"
    :block="$block"
    target="_blank"
    rel="noopener noreferrer"
    :class="$attributes->get('class')"
>
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5Z" />
    </svg>
    {{ $label ?? __('common.whatsapp') }}
</x-ui.button>
