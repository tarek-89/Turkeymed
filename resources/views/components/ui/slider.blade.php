{{-- Horizontal scroll-snap slider. Slides are passed in the default slot and
     must be flex-none with a fixed width (and snap-start). Wired by the shared
     [data-carousel] JS in app.js (prev/next + swipe, RTL-aware). --}}
<div data-carousel {{ $attributes->merge(['class' => 'relative']) }}>
    <div data-carousel-track class="no-scrollbar mx-auto flex w-fit max-w-full snap-x snap-mandatory gap-6 overflow-x-auto scroll-smooth py-1">
        {{ $slot }}
    </div>

    <button
        type="button"
        data-carousel-prev
        aria-label="{{ __('common.previous') }}"
        class="absolute -start-2 top-1/2 z-10 hidden h-11 w-11 -translate-y-1/2 place-items-center rounded-full border border-line bg-white text-ink shadow-lg transition duration-150 hover:bg-surface sm:grid"
    >
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="rtl:-scale-x-100">
            <path d="M19 12H5M11 18l-6-6 6-6" />
        </svg>
    </button>
    <button
        type="button"
        data-carousel-next
        aria-label="{{ __('common.next') }}"
        class="absolute -end-2 top-1/2 z-10 hidden h-11 w-11 -translate-y-1/2 place-items-center rounded-full border border-line bg-white text-ink shadow-lg transition duration-150 hover:bg-surface sm:grid"
    >
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="rtl:-scale-x-100">
            <path d="M5 12h14M13 6l6 6-6 6" />
        </svg>
    </button>
</div>
