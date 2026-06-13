<x-layout.app :title="config('site.brand') . ' — ' . __('nav.blog')">
    <div class="mx-auto max-w-5xl px-4 py-10">
        <h1 class="mb-8 text-3xl font-bold text-slate-900">Blog</h1>

        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($posts as $post)
                <a href="{{ $post->url() }}" class="group block overflow-hidden rounded-xl border border-slate-200 transition hover:shadow-md">
                    @if ($post->featuredImageUrl())
                        <img src="{{ $post->featuredImageUrl() }}" alt="{{ $post->title }}"
                             class="h-44 w-full object-cover" loading="lazy">
                    @else
                        <div class="h-44 w-full bg-slate-100"></div>
                    @endif
                    <div class="p-4">
                        <h2 class="font-semibold leading-snug text-slate-900 group-hover:text-teal-700">
                            {{ $post->title }}
                        </h2>
                        @if ($post->published_at)
                            <p class="mt-2 text-xs text-slate-500">
                                {{ $post->published_at->translatedFormat('F j, Y') }}
                            </p>
                        @endif
                    </div>
                </a>
            @empty
                <p class="text-slate-500">No posts yet.</p>
            @endforelse
        </div>

        <div class="mt-10">
            {{ $posts->links() }}
        </div>
    </div>
</x-layout.app>
