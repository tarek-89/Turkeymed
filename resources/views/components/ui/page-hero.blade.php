@props([
    'eyebrow' => null,
    'title' => null,
])

{{--
    Split page hero: text column (eyebrow, h1, lead, actions, meta) + optional media column.
    Slots: default = lead paragraph, actions, meta, media.
--}}
<div {{ $attributes->merge(['class' => 'grid items-center gap-10 '.(isset($media) ? 'lg:grid-cols-[1.05fr_0.95fr]' : '')]) }}>
    <div>
        @if ($eyebrow)
            <x-ui.eyebrow class="mb-3 block">{{ $eyebrow }}</x-ui.eyebrow>
        @endif

        @if ($title)
            <x-ui.heading level="h1">{{ $title }}</x-ui.heading>
        @endif

        @if ($slot->isNotEmpty())
            <p class="lead measure mt-4">{{ $slot }}</p>
        @endif

        @isset($actions)
            <div class="mt-6 flex flex-wrap gap-3">{{ $actions }}</div>
        @endisset

        @isset($meta)
            <div class="mt-5 flex flex-wrap items-center gap-5 text-sm text-muted">{{ $meta }}</div>
        @endisset
    </div>

    @isset($media)
        <div class="min-w-0">{{ $media }}</div>
    @endisset
</div>
