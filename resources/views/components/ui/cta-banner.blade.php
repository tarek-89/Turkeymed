@props([
    'title' => null,
])

{{-- Full-width gradient call-to-action band. Slots: default = supporting text, actions. --}}
<div {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-2xl bg-[linear-gradient(135deg,var(--color-navy-800),var(--color-navy-700)_40%,var(--color-cyan-700))] p-[clamp(36px,5vw,52px)] text-center text-white']) }}>
    @if ($title)
        <h2 class="text-[clamp(1.7rem,4vw,2.25rem)] font-extrabold leading-[1.1] tracking-[-0.025em] text-white">{{ $title }}</h2>
    @endif

    @if ($slot->isNotEmpty())
        <p class="mx-auto mt-3 max-w-[520px] text-lg text-white/75">{{ $slot }}</p>
    @endif

    @isset($actions)
        <div class="mt-7 flex flex-wrap items-center justify-center gap-3">{{ $actions }}</div>
    @endisset
</div>
