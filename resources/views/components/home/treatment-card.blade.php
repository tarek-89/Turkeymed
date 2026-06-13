@props([
    /** @var \App\Models\TreatmentCard */
    'card',
])

@php
    $title = $card->translate('title');
    $description = $card->translate('description');
    $badge = $card->translate('badge');
    $footnote = $card->translate('footnote');
    $href = $card->url ?: \App\Support\Navigation::contactUrl();
@endphp

@if ($card->variant === 'feature')
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'group flex flex-col justify-between rounded-2xl bg-[linear-gradient(160deg,var(--color-navy-800),var(--color-navy-700)_45%,var(--color-cyan-700))] p-7 text-white shadow-lg transition duration-150 hover:-translate-y-1 lg:row-span-2']) }}>
        <div>
            @if ($card->icon)
                <x-ui.icon-box variant="light"><x-ui.icon :name="$card->icon" class="text-white" /></x-ui.icon-box>
            @endif
            @if ($badge)
                <x-ui.badge variant="translucent" class="mt-5">{{ $badge }}</x-ui.badge>
            @endif
            <h3 class="mt-3 text-2xl font-bold text-white">{{ $title }}</h3>
            @if ($description)
                <p class="mt-2 text-white/80">{{ $description }}</p>
            @endif
        </div>
        <div class="mt-8 flex items-center justify-between">
            <span class="font-bold text-white">{{ $footnote }}</span>
            <span class="grid h-10 w-10 place-items-center rounded-full bg-cyan-400 text-navy-900" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" class="rtl:-scale-x-100"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
            </span>
        </div>
    </a>
@elseif ($card->variant === 'cta')
    <x-ui.card variant="soft" {{ $attributes->merge(['class' => 'flex flex-col justify-between']) }}>
        <div>
            <h3 class="text-xl font-bold text-ink">{{ $title }}</h3>
            @if ($description)
                <p class="mt-2 text-muted">{{ $description }}</p>
            @endif
        </div>
        <x-ui.link-arrow :href="$href" class="mt-6">{{ $footnote ?: __('nav.cta') }}</x-ui.link-arrow>
    </x-ui.card>
@else
    <x-ui.card :href="$href" :interactive="true" {{ $attributes->merge(['class' => 'group']) }}>
        @if ($card->icon)
            <x-ui.icon-box variant="default"><x-ui.icon :name="$card->icon" class="text-cyan-800" /></x-ui.icon-box>
        @endif
        <h3 class="mt-5 text-xl font-bold text-ink transition duration-150 group-hover:text-cyan-800">{{ $title }}</h3>
        @if ($description)
            <p class="mt-2 text-muted">{{ $description }}</p>
        @endif
    </x-ui.card>
@endif
