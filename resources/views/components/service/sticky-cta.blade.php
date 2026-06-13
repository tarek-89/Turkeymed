@props([
    'price' => null, // formatted "from" price, e.g. "€1,500" (omitted until pricing data exists)
])

@php
    $number = preg_replace('/\D/', '', (string) config('site.whatsapp'));
@endphp

{{-- Mobile-only sticky conversion bar. Pair with bodyClass="max-lg:pb-24" on x-layout.app. --}}
<div {{ $attributes->merge(['class' => 'fixed inset-x-3 bottom-3 z-[95] flex items-center gap-2.5 rounded-lg border border-line bg-white p-2.5 ps-4 shadow-lg lg:hidden']) }}>
    @if ($price)
        <div class="flex-none leading-tight">
            <span class="block text-xs text-muted">{{ __('services.from') }}</span>
            <span class="block font-bold text-cyan-800">{{ $price }}</span>
        </div>
    @endif

    <x-ui.button :href="\App\Support\Navigation::contactUrl()" variant="primary" class="flex-1">
        {{ __('nav.cta') }}
    </x-ui.button>

    <a
        href="https://wa.me/{{ $number }}"
        target="_blank"
        rel="noopener noreferrer"
        class="grid h-12 w-12 flex-none place-items-center rounded-md bg-cyan-400 text-navy-900"
        aria-label="{{ __('common.whatsapp_aria') }}"
    >
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5Z" />
        </svg>
    </a>
</div>
