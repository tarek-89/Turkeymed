@props([
    'before', // URL of the "before" image
    'after', // URL of the "after" image
    'beforeLabel' => null,
    'afterLabel' => null,
    'alt' => '',
])

{{--
    Accessible before/after comparison. The real control is an <input type="range">
    covering the image (keyboard + touch + screen-reader friendly); JS mirrors its
    value into --ba-pos, which drives the clip-path and the decorative handle.
    The widget is inherently directional, so it is pinned to LTR in RTL locales too.
--}}
<div
    dir="ltr"
    data-ba
    style="--ba-pos: 50%"
    {{ $attributes->merge(['class' => 'relative aspect-[4/3] select-none overflow-hidden rounded-xl border border-line bg-surface']) }}
>
    {{-- After (base layer) --}}
    <img src="{{ $after }}" alt="{{ $alt }}" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
    <span class="absolute right-3 top-3 z-10 rounded-full bg-navy-900/70 px-3 py-1 font-mono text-xs text-white">
        {{ $afterLabel ?? __('patient_results.after') }}
    </span>

    {{-- Before (clipped overlay) --}}
    <div class="absolute inset-0 [clip-path:inset(0_calc(100%-var(--ba-pos))_0_0)]">
        <img src="{{ $before }}" alt="" aria-hidden="true" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
        <span class="absolute bottom-3 left-3 rounded-full bg-navy-900/70 px-3 py-1 font-mono text-xs text-white">
            {{ $beforeLabel ?? __('patient_results.before') }}
        </span>
    </div>

    {{-- Divider + handle (decorative, follows --ba-pos) --}}
    <div class="pointer-events-none absolute inset-y-0 left-[var(--ba-pos)] z-10 w-0.5 -translate-x-1/2 bg-white shadow-md" aria-hidden="true"></div>
    <div class="pointer-events-none absolute left-[var(--ba-pos)] top-1/2 z-10 grid h-11 w-11 -translate-x-1/2 -translate-y-1/2 place-items-center rounded-full bg-[linear-gradient(135deg,var(--color-cyan-400),var(--color-cyan-700))] shadow-glow" aria-hidden="true">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 6 3 12l6 6M15 6l6 6-6 6" />
        </svg>
    </div>

    {{-- The actual control --}}
    <input
        type="range"
        data-ba-range
        min="0"
        max="100"
        step="1"
        value="50"
        aria-label="{{ __('patient_results.compare_aria') }}"
        class="ba-range absolute inset-0 z-20 h-full w-full cursor-ew-resize appearance-none bg-transparent"
    >
</div>
