<footer class="bg-navy-900 pb-6 pt-12 text-white/70">
    <x-ui.container>
        <div class="grid gap-8 border-b border-white/10 pb-8 lg:grid-cols-[1.6fr_1fr_1fr_1.3fr]">
            {{-- Brand + tagline --}}
            <div>
                <a href="{{ \App\Support\Navigation::homeUrl() }}" class="mb-3.5 flex items-center gap-2.5 text-[1.2rem] font-extrabold text-white">
                    <x-layout.brand-lockup :dark="true" height="h-12" />
                </a>
                <p class="max-w-[280px] text-sm leading-relaxed text-white/60">{{ __('footer.tagline') }}</p>
            </div>

            {{-- Treatments --}}
            @php($footerTreatments = \App\Support\Navigation::footerTreatments())
            @if ($footerTreatments !== [])
                <nav aria-label="{{ __('footer.treatments_heading') }}">
                    <h2 class="mb-3.5 text-xs font-bold uppercase tracking-[0.1em] text-white/50">{{ __('footer.treatments_heading') }}</h2>
                    @foreach ($footerTreatments as $item)
                        <a href="{{ $item['url'] }}" class="block py-1.5 text-sm transition-colors duration-150 hover:text-white">{{ $item['label'] }}</a>
                    @endforeach
                </nav>
            @endif

            {{-- Company --}}
            <nav aria-label="{{ __('footer.company_heading') }}">
                <h2 class="mb-3.5 text-xs font-bold uppercase tracking-[0.1em] text-white/50">{{ __('footer.company_heading') }}</h2>
                @foreach (\App\Support\Navigation::footerCompany() as $item)
                    <a href="{{ $item['url'] }}" class="block py-1.5 text-sm transition-colors duration-150 hover:text-white">{{ $item['label'] }}</a>
                @endforeach
            </nav>

            {{-- Connect --}}
            @php($footerSocial = \App\Models\SocialLink::published()->orderBy('sort_order')->get())
            <div>
                <h2 class="mb-3.5 text-xs font-bold uppercase tracking-[0.1em] text-white/50">{{ __('footer.connect_heading') }}</h2>
                <p class="mb-4 text-sm text-white/60">{{ __('footer.connect_text') }}</p>

                @if ($footerSocial->isNotEmpty())
                    <x-ui.social-links :links="$footerSocial" />
                @else
                    <x-ui.whatsapp-button variant="accent" />
                @endif
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
