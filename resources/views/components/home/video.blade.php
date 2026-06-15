@props([
    /** @var \App\Models\Video */
    'video',
])

@php
    $id = $video->youtubeId();
    $embed = $video->embedUrl();
    $title = $video->translate('title');
    $ratio = $video->kind === 'short' ? 'aspect-[9/16] max-w-[300px]' : 'aspect-video';
    // hqdefault always exists and is light. Used as a CSS background (not an
    // <img>) so its 4:3 natural ratio never trips the "incorrect aspect ratio"
    // or "unsized image" audits — the ratio is owned by the container instead.
    $poster = $id ? 'https://i.ytimg.com/vi/'.$id.'/hqdefault.jpg' : null;
    $playLabel = trim(__('common.play_video').' '.($title ?? ''));
@endphp

@if ($embed)
    <figure {{ $attributes->merge(['class' => 'flex flex-col']) }}>
        {{-- Lite facade: poster + play button. The real nocookie iframe is
             injected on click (see [data-youtube] handler in app.js). The
             aspect-ratio box reserves space, so swapping in the iframe never
             shifts layout (CLS). --}}
        <div
            data-youtube
            data-youtube-src="{{ $embed }}?autoplay=1&rel=0"
            data-youtube-title="{{ $title ?? 'YouTube video' }}"
            @if ($poster) style="background-image:url('{{ $poster }}')" @endif
            class="{{ $ratio }} relative w-full overflow-hidden rounded-xl border border-line bg-navy-900 bg-cover bg-center"
        >
            <button
                type="button"
                data-youtube-play
                aria-label="{{ $playLabel }}"
                class="group absolute inset-0 grid place-items-center bg-navy-900/20 transition duration-150 hover:bg-navy-900/10 focus-visible:bg-navy-900/10"
            >
                <span class="grid h-16 w-16 place-items-center rounded-full bg-white/95 text-navy-700 shadow-lg transition duration-150 group-hover:scale-105">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M8 5v14l11-7z" />
                    </svg>
                </span>
            </button>
        </div>
        @if ($title)
            <figcaption class="mt-3 text-sm font-semibold text-ink">{{ $title }}</figcaption>
        @endif
    </figure>
@endif
