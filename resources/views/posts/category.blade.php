<x-layout.app
    :title="$category->name.' - '.config('site.brand')"
    :description="__('home.journal_title')"
    :canonical="$posts->currentPage() > 1 ? $category->blogUrl($language).'?page='.$posts->currentPage() : $category->blogUrl($language)"
>
    <x-ui.section :tight="true">
        <x-ui.breadcrumbs
            :items="[
                ['label' => __('nav.blog'), 'href' => \App\Support\Navigation::blogUrl()],
                ['label' => $category->name],
            ]"
            class="pb-5"
        />

        <x-ui.section-heading :eyebrow="__('posts.blog')" :title="$category->name" level="h1" />

        @if ($posts->isEmpty())
            <x-ui.card variant="soft" class="mx-auto max-w-xl text-center">
                <p class="text-muted">{{ __('posts.empty') }}</p>
            </x-ui.card>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <x-blog.post-card :post="$post" />
                @endforeach
            </div>

            @if ($posts->hasPages())
                <div class="mt-10">
                    {{ $posts->onEachSide(1)->links() }}
                </div>
            @endif
        @endif
    </x-ui.section>
</x-layout.app>
