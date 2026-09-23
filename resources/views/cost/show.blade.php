<x-layout.app
    :title="$page->metaTitle($language)"
    :description="$page->metaDescription($language)"
    :canonical="$page->url($language)"
    :og-image="$page->ogImageUrl()"
    :alternates="$page->is_unlisted ? null : $page->alternates()"
    :noindex="$page->is_unlisted"
    :fab="false"
    body-class="max-lg:pb-24"
>
    @php
        $breadcrumbs = array_values(array_filter([
            ['label' => __('common.home'), 'href' => \App\Support\Navigation::homeUrl()],
            $page->category
                ? ['label' => $page->category->translate('name', $language), 'href' => $page->category->serviceUrl($language)]
                : null,
            ['label' => $page->translate('title', $language)],
        ]));
    @endphp

    <script type="application/ld+json">{!! json_encode(\App\Support\Seo\SchemaBuilder::costPage($page, $language), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>

    <x-ui.container>
        <x-ui.breadcrumbs :items="$breadcrumbs" class="pt-5" />
    </x-ui.container>

    {{-- The H1 lives in the calculator hero; when that section is hidden, the page still needs one. --}}
    @unless ($sections->contains(fn ($section) => $section->key === \App\Support\Cost\SectionKey::Calculator->value))
        <x-ui.section :tight="true">
            <x-ui.heading level="h1">{{ $page->translate('title', $language) }}</x-ui.heading>
        </x-ui.section>
    @endunless

    {{-- Sections, in admin order. Each key renders its own x-cost.* component. --}}
    @foreach ($sections as $section)
        <x-dynamic-component
            :component="$section->sectionKey()->component()"
            :page="$page"
            :section="$section"
            :language="$language"
        />
    @endforeach

    {{-- The calculator section brings its own mobile bar; other pages get the standard one. --}}
    @unless ($sections->contains(fn ($section) => $section->key === \App\Support\Cost\SectionKey::Calculator->value))
        <x-service.sticky-cta />
    @endunless
</x-layout.app>
