<x-layout.app
    :title="$service->metaTitle()"
    :description="$service->metaDescription()"
    :canonical="$service->url()"
    og-type="article"
    :og-image="$service->featuredImageUrl()"
    :alternates="$versions->map->url()->all()"
    :fab="false"
    body-class="max-lg:pb-24"
>
    @php
        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'MedicalProcedure',
            'name' => $service->title,
            'inLanguage' => $service->language,
            'url' => $service->url(),
            'description' => $service->metaDescription(),
            'image' => $service->featuredImageUrl(),
            'provider' => [
                '@type' => 'MedicalOrganization',
                'name' => config('app.name'),
                'url' => url('/'),
            ],
        ];

        $breadcrumbs = array_values(array_filter([
            ['label' => __('common.home'), 'href' => \App\Support\Navigation::homeUrl()],
            $service->category
                ? ['label' => $service->category->name, 'href' => $service->category->url($service->language)]
                : null,
            ['label' => $service->title],
        ]));
    @endphp
    <script type="application/ld+json">{!! json_encode(array_filter($jsonLd, fn ($v) => $v !== null), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

    <x-ui.container>
        <x-ui.breadcrumbs :items="$breadcrumbs" class="pt-5" />
    </x-ui.container>

    {{-- Hero --}}
    <x-ui.section :tight="true">
        <x-ui.page-hero :eyebrow="$service->category?->name" :title="$service->title">
            {{ $service->excerpt }}

            <x-slot:actions>
                <x-ui.button :href="\App\Support\Navigation::contactUrl()" variant="primary">
                    {{ __('nav.cta_long') }}
                </x-ui.button>
                <x-ui.whatsapp-button variant="secondary" />
            </x-slot:actions>

            @if ($service->featuredImageUrl())
                <x-slot:media>
                    <img
                        src="{{ $service->featuredImageUrl() }}"
                        alt="{{ $service->title }}"
                        class="aspect-[4/3] w-full rounded-xl border border-line object-cover"
                        loading="eager"
                        fetchpriority="high"
                    >
                </x-slot:media>
            @endif
        </x-ui.page-hero>
    </x-ui.section>

    {{-- Body + sidebar --}}
    <x-ui.section :tight="true">
        <x-service.layout>
            <x-ui.prose>
                {!! $service->body !!}
            </x-ui.prose>

            <x-slot:aside>
                <x-service.consultation-card />
                <x-service.related-links :services="$related" :heading="$service->category?->name" />
            </x-slot:aside>
        </x-service.layout>
    </x-ui.section>

    {{-- Real results: before/after carousel (hidden until results exist) --}}
    <x-service.results :results="$results" />

    {{-- Bottom CTA --}}
    <x-ui.section>
        <x-ui.cta-banner :title="__('services.assessment_title')">
            {{ __('services.assessment_text') }}

            <x-slot:actions>
                <x-ui.button :href="\App\Support\Navigation::contactUrl()" variant="accent">
                    {{ __('services.assessment_cta') }}
                </x-ui.button>
                <x-ui.whatsapp-button variant="translucent" />
            </x-slot:actions>
        </x-ui.cta-banner>
    </x-ui.section>

    <x-service.sticky-cta />
</x-layout.app>
