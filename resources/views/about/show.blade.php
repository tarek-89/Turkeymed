<x-layout.app
    :title="$heading.' - '.config('site.brand')"
    :description="$text"
    :canonical="$language === \App\Support\Locale::DEFAULT ? route('about') : route('about.localized', $language)"
>
    {{-- Hero: heading + intro + photo slider --}}
    <x-ui.section :tight="true">
        <x-ui.page-hero :eyebrow="__('about.eyebrow')" :title="$heading">
            {{ $text }}

            @if (! empty($images))
                <x-slot:media>
                    <x-ui.image-carousel :images="$images" :alt="$heading" />
                </x-slot:media>
            @endif
        </x-ui.page-hero>
    </x-ui.section>

    {{-- Our story --}}
    @if ($storyTitle || $storyText)
        <x-ui.section :tight="true">
            <x-ui.card class="p-[clamp(24px,4vw,40px)]">
                <div class="grid items-start gap-8 lg:grid-cols-[0.8fr_1.2fr] lg:gap-12">
                    <div>
                        <x-ui.eyebrow class="block">{{ __('about.story_eyebrow') }}</x-ui.eyebrow>
                        <x-ui.heading level="h2" class="mt-2.5">{{ $storyTitle }}</x-ui.heading>
                    </div>

                    <div class="space-y-4">
                        @foreach (preg_split('/\n\s*\n/', trim((string) $storyText)) as $paragraph)
                            <p class="lead">{{ $paragraph }}</p>
                        @endforeach
                    </div>
                </div>
            </x-ui.card>
        </x-ui.section>
    @endif

    {{-- What we stand for --}}
    @if ($promises->isNotEmpty())
        <x-ui.section :tight="true">
            <x-ui.section-heading
                :eyebrow="__('about.promises_eyebrow')"
                :title="__('about.promises_title')"
                align="center"
            />

            <div class="grid gap-6 md:grid-cols-3">
                @foreach ($promises as $promise)
                    <x-ui.card>
                        <x-ui.icon-box variant="default">
                            <x-ui.icon :name="$promise->icon" class="text-cyan-800" />
                        </x-ui.icon-box>

                        <h3 class="mt-5 text-xl font-bold text-ink">{{ $promise->translate('title') }}</h3>
                        <p class="mt-2 leading-relaxed text-muted">{{ $promise->translate('text') }}</p>
                    </x-ui.card>
                @endforeach
            </div>
        </x-ui.section>
    @endif

    {{-- Numbers widget --}}
    @if ($stats->isNotEmpty())
        <x-ui.section :tight="true">
            <div class="grid gap-8 rounded-2xl bg-[linear-gradient(135deg,var(--color-navy-800),var(--color-navy-700)_40%,var(--color-cyan-700))] px-6 py-12 sm:grid-cols-2 lg:grid-cols-4 lg:px-12">
                @foreach ($stats as $stat)
                    <x-ui.stat :value="$stat->value" :label="$stat->translate('label')" variant="light" />
                @endforeach
            </div>
        </x-ui.section>
    @endif

    {{-- CTA --}}
    <x-ui.section>
        <x-ui.cta-banner :title="__('about.cta_title')">
            {{ __('about.cta_text') }}

            <x-slot:actions>
                <x-ui.button :href="\App\Support\Navigation::contactUrl()" variant="accent">
                    {{ __('nav.cta_long') }}
                </x-ui.button>
                <x-ui.whatsapp-button variant="translucent" />
            </x-slot:actions>
        </x-ui.cta-banner>
    </x-ui.section>
</x-layout.app>
