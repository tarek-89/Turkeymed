<x-layout.app
    :title="$author->nameWithCredentials().' - '.config('site.brand')"
    :description="$author->translate('bio', $language) ?? $author->nameWithCredentials()"
    :canonical="$author->url($language)"
>
    @php
        $person = array_merge(['@context' => 'https://schema.org'], \App\Support\Seo\SchemaBuilder::person($author, $language));
    @endphp
    <script type="application/ld+json">{!! json_encode($person, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>

    <x-ui.container>
        <x-ui.breadcrumbs
            class="pt-5"
            :items="[
                ['label' => __('common.home'), 'href' => \App\Support\Navigation::homeUrl()],
                ['label' => $author->name],
            ]"
        />
    </x-ui.container>

    <x-ui.section :tight="true">
        <div class="mx-auto flex max-w-3xl flex-col items-center gap-5 text-center sm:flex-row sm:items-start sm:text-start">
            @if ($author->photoUrl())
                <img
                    src="{{ $author->photoUrl() }}"
                    alt="{{ $author->name }}"
                    width="112"
                    height="112"
                    class="h-28 w-28 shrink-0 rounded-full object-cover shadow-md"
                >
            @endif
            <div>
                <x-ui.heading level="h1" size="h2">{{ $author->name }}</x-ui.heading>
                @php($role = trim(implode(' · ', array_filter([$author->title, $author->credentials]))))
                @if ($role !== '')
                    <p class="mt-1 font-semibold text-cyan-800">{{ $role }}</p>
                @endif
                @if ($author->translate('bio', $language))
                    <p class="mt-4 text-muted">{{ $author->translate('bio', $language) }}</p>
                @endif
                @if ($author->profileLinks() !== [])
                    <div class="mt-4 flex flex-wrap justify-center gap-3 sm:justify-start">
                        @foreach ($author->profileLinks() as $link)
                            <a href="{{ $link }}" rel="noopener noreferrer me" target="_blank" class="text-sm font-semibold text-cyan-800 hover:underline">{{ parse_url($link, PHP_URL_HOST) ?? $link }}</a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </x-ui.section>

    @if ($posts->isNotEmpty())
        <x-ui.section :tight="true">
            <x-ui.section-heading :title="__('posts.blog')" level="h2" />
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <x-blog.post-card :post="$post" />
                @endforeach
            </div>
        </x-ui.section>
    @endif

    @if ($services->isNotEmpty())
        <x-ui.section :tight="true">
            <x-ui.section-heading :title="__('nav.treatments')" level="h2" />
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($services as $service)
                    <x-ui.card :href="$service->url()" :interactive="true" class="group flex flex-col">
                        <h3 class="text-lg font-bold text-ink transition-colors duration-150 group-hover:text-cyan-800">{{ $service->title }}</h3>
                        @if ($service->excerpt)
                            <p class="mt-2 text-sm text-muted">{{ $service->excerpt }}</p>
                        @endif
                    </x-ui.card>
                @endforeach
            </div>
        </x-ui.section>
    @endif
</x-layout.app>
