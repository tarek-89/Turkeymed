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
        $breadcrumbs = array_values(array_filter([
            ['label' => __('common.home'), 'href' => \App\Support\Navigation::homeUrl()],
            ['label' => __('nav.treatments'), 'href' => \App\Support\Navigation::servicesUrl()],
            $service->category
                ? ['label' => $service->category->name, 'href' => $service->category->serviceUrl($service->language)]
                : null,
            ['label' => $service->title],
        ]));
    @endphp
    <script type="application/ld+json">{!! json_encode(\App\Support\Seo\SchemaBuilder::medicalWebPage($service), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>

    @if ($service->featuredImageUrl())
        @push('head')
            <link rel="preload" as="image" href="{{ $service->featuredImageUrl() }}" fetchpriority="high">
        @endpush
    @endif

    <x-ui.container>
        <x-ui.breadcrumbs :items="$breadcrumbs" class="pt-5" />
    </x-ui.container>

    {{-- Hero --}}
    <x-ui.section :tight="true">
        <x-ui.page-hero :eyebrow="$service->category?->name" :title="$service->title">
            {{ $service->summary ?: $service->excerpt }}

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
                        @if ($service->featuredImageSrcset())
                            srcset="{{ $service->featuredImageSrcset() }}"
                            sizes="(max-width: 768px) 100vw, 560px"
                        @endif
                        alt="{{ $service->title }}"
                        width="1200"
                        height="900"
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
            <x-content.byline
                class="mb-6"
                :author="$service->createdBy"
                :author-name="$service->author"
                :updated="$service->updated_at"
                :language="$service->language"
            />

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

    @if ($service->faqList())
        <x-ui.section :tight="true">
            <x-content.faq :items="$service->faqList()" />
        </x-ui.section>
    @endif

    @php($relatedPosts = $service->relatedPosts())
    @if ($relatedPosts->isNotEmpty())
        <x-ui.section :tight="true">
            <x-ui.section-heading :title="__('content.related_articles')" level="h2" />

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($relatedPosts as $relatedPost)
                    <x-blog.post-card :post="$relatedPost" />
                @endforeach
            </div>
        </x-ui.section>
    @endif

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
