@props([
    /** @var \App\Models\CostPage */
    'page',
    /** @var \App\Models\CostPageSection */
    'section',
    'language',
])

{{-- Related articles from the page's category, using the site's post cards. --}}
@php
    $count = max(1, (int) ($section->text('posts_count') ?: 3));
    $posts = $page->relatedPosts($language, $count);
@endphp

@if ($posts->isNotEmpty())
    <x-ui.section :tight="true" id="blog">
        <x-cost.section-heading :section="$section">
            @if ($section->text('link_label') && $page->category)
                <x-slot:action>
                    <x-ui.link-arrow :href="$page->category->blogUrl($language)">{{ $section->text('link_label') }}</x-ui.link-arrow>
                </x-slot:action>
            @endif
        </x-cost.section-heading>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($posts as $post)
                <x-blog.post-card :post="$post" />
            @endforeach
        </div>
    </x-ui.section>
@endif
