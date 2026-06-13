@props([
    /** @var list<string> Image URLs. */
    'images' => [],
    'alt' => '',
    'ratio' => 'aspect-[4/3]',
])

@php
    $images = array_values(array_filter($images));
@endphp

@if (count($images) === 0)
    {{-- Nothing to show: a neutral placeholder keeps the layout intact. --}}
    <div {{ $attributes->merge(['class' => $ratio.' w-full rounded-2xl border border-line bg-cyan-50']) }} aria-hidden="true"></div>
@elseif (count($images) === 1)
    <img
        src="{{ $images[0] }}"
        alt="{{ $alt }}"
        loading="lazy"
        {{ $attributes->merge(['class' => $ratio.' w-full rounded-2xl border border-line object-cover']) }}
    >
@else
    <div data-carousel {{ $attributes->merge(['class' => 'relative']) }}>
        <div data-carousel-track class="no-scrollbar flex snap-x snap-mandatory overflow-x-auto scroll-smooth rounded-2xl">
            @foreach ($images as $image)
                <img
                    src="{{ $image }}"
                    alt="{{ $loop->first ? $alt : '' }}"
                    @if (! $loop->first) aria-hidden="true" @endif
                    loading="lazy"
                    class="{{ $ratio }} w-full flex-none snap-start border border-line object-cover"
                >
            @endforeach
        </div>

        <button
            type="button"
            data-carousel-prev
            aria-label="{{ __('common.previous') }}"
            class="absolute start-3 top-1/2 grid h-10 w-10 -translate-y-1/2 place-items-center rounded-full bg-white/90 text-ink shadow-md transition duration-150 hover:bg-white"
        >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="rtl:-scale-x-100">
                <path d="M19 12H5M11 18l-6-6 6-6" />
            </svg>
        </button>
        <button
            type="button"
            data-carousel-next
            aria-label="{{ __('common.next') }}"
            class="absolute end-3 top-1/2 grid h-10 w-10 -translate-y-1/2 place-items-center rounded-full bg-white/90 text-ink shadow-md transition duration-150 hover:bg-white"
        >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="rtl:-scale-x-100">
                <path d="M5 12h14M13 6l6 6-6 6" />
            </svg>
        </button>
    </div>
@endif
