{{-- Sticky site header. The shadow is strengthened on scroll via app.js. --}}
@props([
    /** @var array<string, string>|null Passed through to the language switcher. */
    'alternates' => null,
])

<header class="sticky top-0 z-[100] py-3.5" data-site-header>
    <x-ui.container>
        <nav
            class="flex items-center justify-between gap-4 rounded-lg border border-line bg-white/80 py-2.5 pe-3 ps-[18px] shadow-md backdrop-blur-md transition-shadow duration-300"
            aria-label="{{ __('nav.primary_label') }}"
        >
            <a href="{{ \App\Support\Navigation::homeUrl() }}" class="flex items-center gap-2.5 text-[1.2rem] font-extrabold tracking-tight text-navy-700">
                <x-layout.brand-mark />
                {{ config('site.brand') }}
            </a>

            <div class="hidden items-center gap-6 text-[0.92rem] font-semibold text-ink-2 lg:flex">
                @foreach (\App\Support\Navigation::treatmentMenu() as $category)
                    <div class="group relative">
                        <a
                            href="{{ $category['url'] }}"
                            class="inline-flex items-center gap-1 transition-colors duration-150 hover:text-ink"
                            aria-haspopup="true"
                        >
                            {{ $category['label'] }}
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="mt-0.5 transition-transform duration-150 group-hover:rotate-180 group-focus-within:rotate-180">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </a>

                        <div class="invisible absolute start-0 top-full z-50 pt-3 opacity-0 transition duration-150 group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100">
                            <div class="max-h-[70vh] w-64 overflow-auto rounded-md border border-line bg-white p-2 shadow-lg">
                                @foreach ($category['services'] as $service)
                                    <a
                                        href="{{ $service['url'] }}"
                                        class="block rounded-lg px-3 py-2 text-sm font-medium text-ink transition-colors duration-150 hover:bg-surface hover:text-cyan-800"
                                    >{{ $service['label'] }}</a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach

                @foreach (\App\Support\Navigation::primary() as $item)
                    <a
                        href="{{ $item['url'] }}"
                        @if ($item['active']) aria-current="page" @endif
                        class="transition-colors duration-150 hover:text-ink {{ $item['active'] ? 'text-ink' : '' }}"
                    >{{ $item['label'] }}</a>
                @endforeach
            </div>

            <div class="flex items-center gap-2.5">
                <x-ui.language-switcher :alternates="$alternates" />

                <span class="hidden lg:inline-flex">
                    <x-ui.button :href="\App\Support\Navigation::contactUrl()" variant="primary" size="sm">
                        {{ __('nav.cta') }}
                    </x-ui.button>
                </span>

                <button
                    type="button"
                    class="inline-flex h-11 w-11 flex-col items-center justify-center gap-1 rounded-sm border border-line bg-white lg:hidden"
                    data-nav-toggle
                    aria-label="{{ __('nav.open_menu') }}"
                    aria-expanded="false"
                    aria-controls="mobile-drawer"
                >
                    <span class="h-0.5 w-5 rounded bg-ink" aria-hidden="true"></span>
                    <span class="h-0.5 w-5 rounded bg-ink" aria-hidden="true"></span>
                    <span class="h-0.5 w-3.5 rounded bg-ink" aria-hidden="true"></span>
                </button>
            </div>
        </nav>
    </x-ui.container>
</header>
