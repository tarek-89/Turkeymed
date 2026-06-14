{{-- Horizontal scroll-snap slider. Slides are passed in the default slot and
     must be flex-none with a fixed width (and snap-start/center).

     Controls adapt to the viewport:
       • desktop → arrows on the sides, just outside the cards (don't overlap);
       • mobile  → arrows centered below the track.
     Both sets are wired by the shared [data-carousel] JS in app.js (the hidden
     set is display:none, so screen readers only ever see the visible one). --}}
<div data-carousel {{ $attributes }}>
    <div class="relative mx-auto w-fit max-w-full">
        <div data-carousel-track class="no-scrollbar flex max-w-full snap-x snap-mandatory gap-6 overflow-x-auto scroll-smooth py-1">
            {{ $slot }}
        </div>

        {{-- Desktop: side arrows, hugging the cards --}}
        <button
            type="button"
            data-carousel-prev
            aria-label="{{ __('common.previous') }}"
            class="absolute -start-3 top-1/2 hidden h-11 w-11 -translate-y-1/2 place-items-center rounded-full border border-line bg-white text-ink shadow-md transition duration-150 hover:bg-surface sm:grid"
        >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="rtl:-scale-x-100">
                <path d="M19 12H5M11 18l-6-6 6-6" />
            </svg>
        </button>
        <button
            type="button"
            data-carousel-next
            aria-label="{{ __('common.next') }}"
            class="absolute -end-3 top-1/2 hidden h-11 w-11 -translate-y-1/2 place-items-center rounded-full border border-line bg-white text-ink shadow-md transition duration-150 hover:bg-surface sm:grid"
        >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="rtl:-scale-x-100">
                <path d="M5 12h14M13 6l6 6-6 6" />
            </svg>
        </button>
    </div>

    {{-- Mobile: arrows centered below the track --}}
    <div class="mt-5 flex items-center justify-center gap-3 sm:hidden">
        <button
            type="button"
            data-carousel-prev
            aria-label="{{ __('common.previous') }}"
            class="grid h-11 w-11 place-items-center rounded-full border border-line bg-white text-ink shadow-md transition duration-150 hover:bg-surface"
        >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="rtl:-scale-x-100">
                <path d="M19 12H5M11 18l-6-6 6-6" />
            </svg>
        </button>
        <button
            type="button"
            data-carousel-next
            aria-label="{{ __('common.next') }}"
            class="grid h-11 w-11 place-items-center rounded-full border border-line bg-white text-ink shadow-md transition duration-150 hover:bg-surface"
        >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="rtl:-scale-x-100">
                <path d="M5 12h14M13 6l6 6-6 6" />
            </svg>
        </button>
    </div>
</div>
