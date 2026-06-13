@props([
    'icon', // shield|clock|chat|... (x-ui.icon name) — typically chat/phone/mail
    'title',
    'description' => null,
    'value', // display text, e.g. "+90 555 000 00 00"
    'href' => '#',
    'accent' => false, // highlighted (WhatsApp) card on a gradient surface
])

@php
    $surface = $accent
        ? 'bg-[linear-gradient(135deg,var(--color-navy-700),var(--color-cyan-700))] text-white border-transparent'
        : 'bg-white border-line text-ink';
    $descColor = $accent ? 'text-white/80' : 'text-muted';
    $valColor = $accent ? 'text-white' : 'text-cyan-800';
@endphp

<a
    href="{{ $href }}"
    {{ $attributes->merge(['class' => 'group flex flex-col rounded-xl border p-6 shadow-md transition duration-150 hover:-translate-y-1 hover:shadow-lg '.$surface]) }}
>
    <x-ui.icon-box :variant="$accent ? 'light' : 'default'">
        <x-ui.icon :name="$icon" @class(['text-white' => $accent, 'text-cyan-800' => ! $accent]) />
    </x-ui.icon-box>

    <h3 @class(['mt-5 text-lg font-bold', 'text-white' => $accent, 'text-ink' => ! $accent])>{{ $title }}</h3>

    @if ($description)
        <p class="mt-1 text-sm {{ $descColor }}">{{ $description }}</p>
    @endif

    <span class="mt-4 inline-flex items-center gap-1.5 text-sm font-bold {{ $valColor }}">
        {{ $value }}
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="transition duration-150 group-hover:translate-x-0.5 rtl:-scale-x-100 rtl:group-hover:-translate-x-0.5">
            <path d="M5 12h14M13 6l6 6-6 6" />
        </svg>
    </span>
</a>
