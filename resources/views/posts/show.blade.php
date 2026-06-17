<x-layout.app
    :title="$post->metaTitle()"
    :description="$post->metaDescription()"
    :canonical="$post->url()"
    og-type="article"
    :og-image="$post->featuredImageUrl()"
    :alternates="$versions->map->url()->all()"
>
    <script type="application/ld+json">{!! json_encode(\App\Support\Seo\SchemaBuilder::blogPosting($post), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>

    @if ($post->featuredImageUrl())
        @push('head')
            <link rel="preload" as="image" href="{{ $post->featuredImageUrl() }}" fetchpriority="high">
        @endpush
    @endif

    <x-ui.container>
        <x-ui.breadcrumbs
            class="pt-5"
            :items="array_values(array_filter([
                ['label' => __('common.home'), 'href' => \App\Support\Navigation::homeUrl()],
                ['label' => __('posts.blog'), 'href' => \App\Support\Navigation::blogUrl()],
                $post->category
                    ? ['label' => $post->category->name, 'href' => $post->category->blogUrl($post->language)]
                    : null,
                ['label' => $post->title],
            ]))"
        />
    </x-ui.container>

    <x-ui.section :tight="true" as="article">
        {{-- Editorial header: centered, badge + title + meta --}}
        <header class="mx-auto max-w-3xl text-center">
            <x-ui.heading level="h1">{{ $post->title }}</x-ui.heading>

            <div class="mt-5 flex justify-center">
                <span class="inline-flex items-center gap-2 rounded-full border border-line bg-white px-4 py-2 text-sm font-semibold text-ink shadow-md">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--color-cyan-700)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" />
                        <path d="M12 7v5l3 2" />
                    </svg>
                    {{ __('posts.min_read', ['minutes' => $post->readingTime()]) }}
                </span>
            </div>

            <x-content.byline
                class="mt-5 justify-center"
                :author="$post->createdBy"
                :author-name="$post->author"
                :updated="$post->updated_at"
                :language="$post->language"
            />
        </header>

        @if ($post->summary)
            <p class="mx-auto mt-6 max-w-3xl text-center text-lg leading-relaxed text-muted">{{ $post->summary }}</p>
        @endif

        @if ($post->featuredImageUrl())
            <img
                src="{{ $post->featuredImageUrl() }}"
                @if ($post->featuredImageSrcset())
                    srcset="{{ $post->featuredImageSrcset() }}"
                    sizes="(max-width: 1024px) 100vw, 1024px"
                @endif
                alt="{{ $post->title }}"
                width="2100"
                height="900"
                class="mx-auto mt-10 aspect-[21/9] w-full max-w-5xl rounded-2xl object-cover shadow-md"
                loading="eager"
                fetchpriority="high"
            >
        @endif

        <x-ui.prose class="mx-auto mt-10 max-w-3xl">
            {!! $post->body !!}
        </x-ui.prose>

        @if ($post->faqList())
            <x-content.faq :items="$post->faqList()" class="mt-12" />
        @endif
    </x-ui.section>

    @php($relatedServices = $post->relatedServices())
    @if ($relatedServices->isNotEmpty())
        <x-ui.section :tight="true">
            <x-ui.section-heading :title="__('content.related_treatments')" level="h2" />

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($relatedServices as $relatedService)
                    <x-ui.card :href="$relatedService->url()" :interactive="true" class="group flex flex-col">
                        <h3 class="text-lg font-bold text-ink transition-colors duration-150 group-hover:text-cyan-800">{{ $relatedService->title }}</h3>
                        @if ($relatedService->excerpt)
                            <p class="mt-2 text-sm text-muted">{{ $relatedService->excerpt }}</p>
                        @endif
                    </x-ui.card>
                @endforeach
            </div>
        </x-ui.section>
    @endif

    @if ($related->isNotEmpty())
        <x-ui.section :tight="true">
            <x-ui.section-heading :eyebrow="__('posts.blog')" :title="__('posts.keep_reading')" />

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($related as $relatedPost)
                    <x-blog.post-card :post="$relatedPost" />
                @endforeach
            </div>
        </x-ui.section>
    @endif

    <x-ui.section>
        <x-ui.cta-banner :title="__('posts.cta_title')">
            {{ __('posts.cta_text') }}

            <x-slot:actions>
                <x-ui.button :href="\App\Support\Navigation::contactUrl()" variant="accent">
                    {{ __('nav.cta_long') }}
                </x-ui.button>
                <x-ui.whatsapp-button variant="translucent" />
            </x-slot:actions>
        </x-ui.cta-banner>
    </x-ui.section>
</x-layout.app>
