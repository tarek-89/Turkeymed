@props([
    /** @var \App\Models\Post */
    'post',
    'compact' => false, // small horizontal teaser (thumb + title + date) for secondary placements
])

@if ($compact)
    <a href="{{ $post->url() }}" {{ $attributes->merge(['class' => 'group flex items-center gap-4 rounded-lg border border-line bg-white p-3 transition duration-150 hover:shadow-md']) }}>
        @if ($post->featuredImageUrl())
            <img
                src="{{ $post->featuredImageUrl() }}"
                alt=""
                aria-hidden="true"
                class="h-16 w-16 flex-none rounded-md object-cover"
                loading="lazy"
            >
        @else
            <div class="grid h-16 w-16 flex-none place-items-center rounded-md bg-cyan-50" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--color-cyan-700)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20M4 19.5A2.5 2.5 0 0 0 6.5 22H20V2H6.5A2.5 2.5 0 0 0 4 4.5v15Z" />
                </svg>
            </div>
        @endif

        <div class="min-w-0">
            <h3 class="line-clamp-2 text-sm font-semibold text-ink transition duration-150 group-hover:text-cyan-800">
                {{ $post->title }}
            </h3>
            @if ($post->published_at)
                <time datetime="{{ $post->published_at->toDateString() }}" class="mt-1 block text-xs text-muted">
                    {{ $post->published_at->translatedFormat('M j, Y') }}
                </time>
            @endif
        </div>
    </a>
@else
<x-ui.card :href="$post->url()" :interactive="true" :class="'group flex flex-col '.$attributes->get('class')">
    @if ($post->featuredImageUrl())
        <img
            src="{{ $post->featuredImageUrl() }}"
            alt="{{ $post->title }}"
            class="mb-5 aspect-[16/10] w-full rounded-lg object-cover"
            loading="lazy"
        >
    @else
        <div class="mb-5 grid aspect-[16/10] w-full place-items-center rounded-lg bg-cyan-50" aria-hidden="true">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--color-cyan-700)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20M4 19.5A2.5 2.5 0 0 0 6.5 22H20V2H6.5A2.5 2.5 0 0 0 4 4.5v15Z" />
            </svg>
        </div>
    @endif

    <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-muted">
        @if ($post->category)
            <x-ui.badge variant="soft">{{ $post->category->name }}</x-ui.badge>
        @endif
        @if ($post->published_at)
            <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->translatedFormat('M j, Y') }}</time>
        @endif
    </div>

    <h3 class="mt-3 text-lg font-bold leading-snug text-ink transition duration-150 group-hover:text-cyan-800">
        {{ $post->title }}
    </h3>

    @if ($post->excerpt)
        <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-muted">{{ $post->excerpt }}</p>
    @endif

    <span class="mt-auto inline-flex items-center gap-1.5 pt-4 text-sm font-bold text-cyan-800">
        {{ __('posts.read_article') }}
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="transition duration-150 group-hover:translate-x-0.5 rtl:-scale-x-100 rtl:group-hover:-translate-x-0.5">
            <path d="M5 12h14M13 6l6 6-6 6" />
        </svg>
    </span>
</x-ui.card>
@endif
