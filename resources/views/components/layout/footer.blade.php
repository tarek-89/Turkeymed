<footer class="bg-navy-900 pb-6 pt-12 text-white/70">
    <x-ui.container>
        <div class="grid gap-8 border-b border-white/10 pb-8 lg:grid-cols-[1.6fr_1fr_1fr_1.3fr]">
            {{-- Brand + tagline --}}
            <div>
                <a href="{{ \App\Support\Navigation::homeUrl() }}" class="mb-3.5 flex items-center gap-2.5 text-[1.2rem] font-extrabold text-white">
                    <x-layout.brand-mark />
                    {{ config('site.brand') }}
                </a>
                <p class="max-w-[280px] text-sm leading-relaxed text-white/60">{{ __('footer.tagline') }}</p>
            </div>

            {{-- Treatments --}}
            <nav aria-label="{{ __('footer.treatments_heading') }}">
                <h2 class="mb-3.5 text-xs font-bold uppercase tracking-[0.1em] text-white/50">{{ __('footer.treatments_heading') }}</h2>
                @foreach (\App\Support\Navigation::footerTreatments() as $item)
                    <a href="{{ $item['url'] }}" class="block py-1.5 text-sm transition-colors duration-150 hover:text-white">{{ $item['label'] }}</a>
                @endforeach
            </nav>

            {{-- Company --}}
            <nav aria-label="{{ __('footer.company_heading') }}">
                <h2 class="mb-3.5 text-xs font-bold uppercase tracking-[0.1em] text-white/50">{{ __('footer.company_heading') }}</h2>
                @foreach (\App\Support\Navigation::footerCompany() as $item)
                    <a href="{{ $item['url'] }}" class="block py-1.5 text-sm transition-colors duration-150 hover:text-white">{{ $item['label'] }}</a>
                @endforeach
            </nav>

            {{-- Newsletter --}}
            <div>
                <h2 class="mb-3.5 text-xs font-bold uppercase tracking-[0.1em] text-white/50">{{ __('footer.guide_heading') }}</h2>
                <p class="mb-3 text-sm text-white/60">{{ __('footer.guide_text') }}</p>
                {{-- TODO: point action to the newsletter route once built; app.js prevents submit until then. --}}
                <form class="flex gap-2" action="#" method="post" data-newsletter>
                    @csrf
                    <label for="footer-email" class="sr-only">{{ __('forms.email_label') }}</label>
                    <input
                        id="footer-email"
                        type="email"
                        name="email"
                        autocomplete="email"
                        placeholder="{{ __('forms.email_placeholder') }}"
                        class="h-11 flex-1 rounded-sm border border-white/20 bg-white/5 px-3 text-white placeholder:text-white/40"
                    >
                    <button type="submit" class="grid h-11 w-11 flex-none place-items-center rounded-sm bg-cyan-400 text-navy-900" aria-label="{{ __('forms.subscribe') }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M5 12h14M13 6l6 6-6 6" class="rtl:hidden" />
                            <path d="M19 12H5M11 6l-6 6 6 6" class="hidden rtl:block" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3.5 pt-5">
            <p class="text-xs text-white/50">
                {{ __('footer.rights', ['year' => date('Y')]) }}
                <span class="px-1">·</span>
                <a href="#" class="hover:text-white">{{ __('footer.privacy') }}</a>
                <span class="px-1">·</span>
                <a href="#" class="hover:text-white">{{ __('footer.terms') }}</a>
                <span class="px-1">·</span>
                <a href="#" class="hover:text-white">{{ __('footer.cookies') }}</a>
            </p>
            <p class="font-mono text-[0.7rem] text-white/40">
                {{ collect(\App\Support\Locale::codes())->map(fn (string $code) => strtoupper($code))->implode(' · ') }}
            </p>
        </div>
    </x-ui.container>
</footer>
