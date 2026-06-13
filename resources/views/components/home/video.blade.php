@props([
    /** @var \App\Models\Video */
    'video',
])

@php
    $embed = $video->embedUrl();
    $title = $video->translate('title');
    $ratio = $video->kind === 'short' ? 'aspect-[9/16] max-w-[300px]' : 'aspect-video';
@endphp

@if ($embed)
    <figure {{ $attributes->merge(['class' => 'flex flex-col']) }}>
        <div class="{{ $ratio }} w-full overflow-hidden rounded-xl border border-line bg-navy-900">
            <iframe
                src="{{ $embed }}"
                title="{{ $title ?? 'YouTube video' }}"
                loading="lazy"
                class="h-full w-full"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                referrerpolicy="strict-origin-when-cross-origin"
                allowfullscreen
            ></iframe>
        </div>
        @if ($title)
            <figcaption class="mt-3 text-sm font-semibold text-ink">{{ $title }}</figcaption>
        @endif
    </figure>
@endif
