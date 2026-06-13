@php
    $heroTitle = $heroTitle ?: config('site.brand');
@endphp

<x-layout.app
    :title="$heroTitle.' — '.config('site.brand')"
    :description="$heroLead"
    :canonical="$language === \App\Support\Locale::DEFAULT ? route('home') : route('home.localized', $language)"
>
    {{-- HERO --}}
    <x-ui.section :tight="true">
        <div class="grid gap-8 overflow-hidden rounded-2xl bg-[linear-gradient(135deg,var(--color-navy-800),var(--color-navy-700)_45%,var(--color-cyan-700))] p-[clamp(28px,5vw,56px)] text-white lg:grid-cols-2 lg:items-center">
            <div>
                @if ($heroBadge)
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-1.5 text-sm font-semibold">
                        <span class="h-2 w-2 rounded-full bg-cyan-400" aria-hidden="true"></span>
                        {{ $heroBadge }}
                    </span>
                @endif

                <h1 class="mt-5 text-[clamp(2rem,5vw,3rem)] font-extrabold leading-[1.08] tracking-[-0.03em] text-white">
                    {{ $heroTitle }}
                    @if ($heroTitleAccent)
                        <span class="block text-cyan-300">{{ $heroTitleAccent }}</span>
                    @endif
                </h1>

                @if ($heroLead)
                    <p class="measure mt-4 text-lg text-white/80">{{ $heroLead }}</p>
                @endif

                <div class="mt-7 flex flex-wrap gap-3">
                    <x-ui.button :href="\App\Support\Navigation::contactUrl()" variant="accent">
                        {{ __('nav.cta_long') }}
                    </x-ui.button>
                    <x-ui.whatsapp-button variant="translucent" />
                </div>
            </div>

            <div class="relative">
                <x-ui.image-carousel :images="$heroImages" :alt="$heroTitle" ratio="aspect-[4/3]" />

                @if ($heroStatValue)
                    <div class="absolute bottom-4 start-4 rounded-xl border border-white/20 bg-navy-900/60 px-5 py-3 backdrop-blur-md">
                        <div class="text-2xl font-extrabold text-white">{{ $heroStatValue }}</div>
                        @if ($heroStatLabel)
                            <div class="text-sm text-white/75">{{ $heroStatLabel }}</div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </x-ui.section>

    {{-- TREATMENTS --}}
    @if ($treatments->isNotEmpty())
        <x-ui.section :tight="true">
            <x-ui.section-heading :eyebrow="__('home.treatments_eyebrow')" :title="__('home.treatments_title')" />
            <div class="grid gap-6 lg:grid-cols-3">
                @foreach ($treatments as $card)
                    <x-home.treatment-card :card="$card" />
                @endforeach
            </div>
        </x-ui.section>
    @endif

    {{-- STATS --}}
    @if ($stats->isNotEmpty())
        <x-ui.section :tight="true">
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($stats as $stat)
                    <x-ui.stat :value="$stat->value" :label="$stat->translate('label')" />
                @endforeach
            </div>
        </x-ui.section>
    @endif

    {{-- REAL RESULTS (before/after) --}}
    <x-service.results :results="$results" />

    {{-- PATIENT STORIES --}}
    @if ($testimonials->isNotEmpty())
        <x-ui.section :tight="true">
            <x-ui.section-heading :eyebrow="__('home.stories_eyebrow')" :title="__('home.stories_title')" align="center" />
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($testimonials as $testimonial)
                    <x-home.testimonial :testimonial="$testimonial" />
                @endforeach
            </div>
        </x-ui.section>
    @endif

    {{-- HOW IT WORKS --}}
    @if ($steps->isNotEmpty())
        <x-ui.section :tight="true">
            <div class="rounded-2xl bg-[linear-gradient(135deg,var(--color-navy-800),var(--color-navy-700)_60%,var(--color-cyan-800))] p-[clamp(28px,5vw,56px)] text-white">
                <x-ui.eyebrow class="block text-cyan-300">{{ __('home.process_eyebrow') }}</x-ui.eyebrow>
                <h2 class="mt-2.5 text-[clamp(1.7rem,4vw,2.25rem)] font-extrabold leading-[1.1] tracking-[-0.025em] text-white">{{ __('home.process_title') }}</h2>

                <ol class="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($steps as $step)
                        <li>
                            <span class="grid h-12 w-12 place-items-center rounded-md border border-white/20 bg-white/10 text-lg font-extrabold text-cyan-300">{{ $loop->iteration }}</span>
                            <h3 class="mt-4 text-lg font-bold text-white">{{ $step->translate('title') }}</h3>
                            @if ($desc = $step->translate('description'))
                                <p class="mt-2 text-sm leading-relaxed text-white/75">{{ $desc }}</p>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </div>
        </x-ui.section>
    @endif

    {{-- GALLERIES --}}
    @if ($galleries->isNotEmpty())
        <x-ui.section :tight="true">
            <div class="space-y-12">
                @foreach ($galleries as $gallery)
                    <div>
                        <x-ui.section-heading :title="$gallery->translate('title')" level="h2">
                            {{ $gallery->translate('description') }}
                        </x-ui.section-heading>

                        @if ($gallery->layout === 'slider')
                            <x-ui.image-carousel :images="$gallery->imageUrls()" :alt="$gallery->translate('title')" ratio="aspect-video" />
                        @else
                            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
                                @foreach ($gallery->imageUrls() as $image)
                                    <img src="{{ $image }}" alt="{{ $gallery->translate('title') }}" loading="lazy" class="aspect-square w-full rounded-lg border border-line object-cover">
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </x-ui.section>
    @endif

    {{-- VIDEOS --}}
    @if ($videos->isNotEmpty())
        <x-ui.section :tight="true">
            <x-ui.section-heading :eyebrow="__('home.videos_eyebrow')" :title="__('home.videos_title')" />
            <div class="flex flex-wrap items-start gap-6">
                @foreach ($videos as $video)
                    <x-home.video :video="$video" class="{{ $video->kind === 'short' ? 'w-[300px]' : 'w-full sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)]' }}" />
                @endforeach
            </div>
        </x-ui.section>
    @endif

    {{-- INSTAGRAM --}}
    @if ($instagramPosts->isNotEmpty())
        <x-ui.section :tight="true">
            <x-ui.section-heading :eyebrow="__('home.instagram_eyebrow')" :title="__('home.instagram_title')" />
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($instagramPosts as $post)
                    <div class="[&_.instagram-media]:!min-w-0 [&_.instagram-media]:!w-full">{!! $post->embed_code !!}</div>
                @endforeach
            </div>
        </x-ui.section>

        @push('scripts')
            <script async src="https://www.instagram.com/embed.js"></script>
        @endpush
    @endif

    {{-- JOURNAL --}}
    @if ($posts->isNotEmpty())
        <x-ui.section :tight="true">
            <x-ui.section-heading :eyebrow="__('posts.blog')" :title="__('home.journal_title')">
                <x-slot:action>
                    <x-ui.link-arrow :href="$language === \App\Support\Locale::DEFAULT ? route('posts.index') : route('posts.index.localized', $language)">
                        {{ __('home.all_articles') }}
                    </x-ui.link-arrow>
                </x-slot:action>
            </x-ui.section-heading>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <x-blog.post-card :post="$post" />
                @endforeach
            </div>
        </x-ui.section>
    @endif

    {{-- CTA --}}
    <x-ui.section>
        <x-ui.cta-banner :title="$ctaTitle ?: __('home.cta_fallback_title')">
            {{ $ctaText ?: __('home.cta_fallback_text') }}

            <x-slot:actions>
                <x-ui.button :href="\App\Support\Navigation::contactUrl()" variant="accent">
                    {{ __('nav.cta_long') }}
                </x-ui.button>
                <x-ui.whatsapp-button variant="translucent" />
            </x-slot:actions>
        </x-ui.cta-banner>
    </x-ui.section>
</x-layout.app>
