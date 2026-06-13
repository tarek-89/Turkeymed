@props([
    /** @var array<string, string>|null Language code => translated URL (from the page's hreflang alternates). */
    'alternates' => null,
])

@php
    $locales = \App\Support\Locale::options($alternates);
    $current = app()->getLocale();
@endphp

<div class="relative" data-lang>
    <button
        type="button"
        class="inline-flex min-h-[42px] items-center gap-2 rounded-sm border border-line bg-white px-3 py-2 text-sm font-bold"
        data-lang-button
        aria-haspopup="true"
        aria-expanded="false"
        aria-label="{{ __('nav.language_label') }}"
    >
        <span class="uppercase">{{ $current }}</span>
        <span class="-mt-0.5 h-1.5 w-1.5 rotate-45 border-b-2 border-r-2 border-n-400" aria-hidden="true"></span>
    </button>

    <div
        class="absolute end-0 top-[calc(100%+8px)] z-50 hidden max-h-80 w-56 overflow-auto rounded-md border border-line bg-white p-2 shadow-lg"
        data-lang-menu
        role="menu"
        aria-label="{{ __('nav.language_label') }}"
    >
        @foreach ($locales as $loc)
            <a
                href="{{ $loc['url'] }}"
                role="menuitem"
                hreflang="{{ $loc['code'] }}"
                @if ($loc['active']) aria-current="true" @endif
                class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm hover:bg-surface {{ $loc['active'] ? 'font-bold text-navy-700' : 'text-ink' }}"
            >
                <span class="w-6 text-[0.72rem] font-bold uppercase text-n-400">{{ $loc['code'] }}</span>
                <span>{{ $loc['native'] }}</span>
            </a>
        @endforeach
    </div>
</div>
