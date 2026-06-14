<x-layout.app
    :title="__('posts.blog').' - '.config('site.brand')"
    :description="__('home.journal_title')"
    :canonical="$posts->currentPage() > 1 ? url()->current().'?page='.$posts->currentPage() : url()->current()"
>
    <x-ui.section :tight="true">
        <x-ui.section-heading :eyebrow="__('posts.blog')" :title="__('home.journal_title')" />

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
