<x-layout.app
    :title="$category->name.' - '.config('site.brand')"
    :description="__('services.category_meta_description', ['category' => $category->name])"
    :canonical="$category->url($language)"
>
    @php
        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => $category->name,
            'inLanguage' => $language,
            'itemListElement' => $services->values()->map(fn ($service, $index) => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $service->title,
                'url' => $service->url(),
            ])->all(),
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>

    <x-ui.container>
        <x-ui.breadcrumbs
            class="pt-5"
            :items="[
                ['label' => __('common.home'), 'href' => \App\Support\Navigation::homeUrl()],
                ['label' => $category->name],
            ]"
        />
    </x-ui.container>

    <x-ui.section :tight="true">
        <x-ui.section-heading :eyebrow="__('nav.treatments')" :title="$category->name" level="h1">
            {{ trans_choice('services.category_count', $services->count()) }}
        </x-ui.section-heading>

        @if ($services->isEmpty())
            <x-ui.card variant="soft" class="mx-auto max-w-xl text-center">
                <p class="text-muted">{{ __('services.category_empty') }}</p>
                <x-ui.button :href="\App\Support\Navigation::contactUrl()" variant="primary" class="mt-5">
                    {{ __('nav.cta_long') }}
                </x-ui.button>
            </x-ui.card>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($services as $service)
                    <x-ui.card :href="$service->url()" :interactive="true" class="group flex flex-col">
                        @if ($service->featuredImageUrl())
                            <img
                                src="{{ $service->featuredImageUrl() }}"
                                @if ($service->featuredImageSrcset())
                                    srcset="{{ $service->featuredImageSrcset() }}"
                                    sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"
                                @endif
                                alt="{{ $service->title }}"
                                width="1600"
                                height="1000"
                                class="mb-5 aspect-[16/10] w-full rounded-lg object-cover"
                                loading="lazy"
                            >
                        @else
                            <div class="mb-5 grid aspect-[16/10] w-full place-items-center rounded-lg bg-[linear-gradient(135deg,var(--color-navy-800),var(--color-navy-700)_40%,var(--color-cyan-700))]" aria-hidden="true">
                                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.85)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 5v14M5 12h14" />
                                </svg>
                            </div>
                        @endif

                        <h2 class="text-lg font-bold text-ink transition duration-150 group-hover:text-cyan-800">
                            {{ $service->title }}
                        </h2>

                        @if ($service->excerpt)
                            <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-muted">{{ $service->excerpt }}</p>
                        @endif

                        <span class="mt-auto inline-flex items-center gap-1.5 pt-4 text-sm font-bold text-cyan-800">
                            {{ __('services.view_service') }}
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="transition duration-150 group-hover:translate-x-0.5 rtl:-scale-x-100 rtl:group-hover:-translate-x-0.5">
                                <path d="M5 12h14M13 6l6 6-6 6" />
                            </svg>
                        </span>
                    </x-ui.card>
                @endforeach
            </div>
        @endif
    </x-ui.section>

    @if ($posts->isNotEmpty())
        <x-ui.section :tight="true">
            <x-ui.section-heading :eyebrow="__('posts.blog')" :title="__('posts.from_the_blog')" level="h2" />

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <x-blog.post-card :post="$post" :compact="true" />
                @endforeach
            </div>
        </x-ui.section>
    @endif

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
</x-layout.app>
