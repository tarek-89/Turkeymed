<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PostController extends Controller
{
    /** Blog index, default language: /blog */
    public function index(): View
    {
        return $this->renderIndex(Post::DEFAULT_LANGUAGE);
    }

    /** Blog index for a locale: /{locale}/blog */
    public function indexLocalized(string $locale): View
    {
        abort_unless($this->localeExists($locale), 404);

        return $this->renderIndex($locale);
    }

    /** Blog category listing: /blog/{categorySlug} */
    public function category(string $categorySlug): View
    {
        return $this->renderCategory(Post::DEFAULT_LANGUAGE, $categorySlug);
    }

    /** Localized blog category listing: /{locale}/blog/{categorySlug} */
    public function categoryLocalized(string $locale, string $categorySlug): View
    {
        abort_unless($this->localeExists($locale), 404);

        return $this->renderCategory($locale, $categorySlug);
    }

    /** Blog post: /blog/{categorySlug}/{slug} */
    public function show(string $categorySlug, string $slug): View|RedirectResponse
    {
        return $this->renderShow(Post::DEFAULT_LANGUAGE, $categorySlug, $slug);
    }

    /** Localized blog post: /{locale}/blog/{categorySlug}/{slug} */
    public function showLocalized(string $locale, string $categorySlug, string $slug): View|RedirectResponse
    {
        return $this->renderShow($locale, $categorySlug, $slug);
    }

    private function renderIndex(string $language): View
    {
        $posts = Post::published()
            ->with('category')
            ->language($language)
            ->orderByDesc('published_at')
            ->paginate(12);

        return view('posts.index', [
            'posts' => $posts,
            'language' => $language,
        ]);
    }

    private function renderCategory(string $language, string $categorySlug): View
    {
        $category = $this->resolveCategory($categorySlug);

        $posts = Post::published()
            ->with('category')
            ->language($language)
            ->when(
                $category->exists,
                fn ($query) => $query->where('service_category_id', $category->id),
                fn ($query) => $query->whereNull('service_category_id'),
            )
            ->orderByDesc('published_at')
            ->paginate(12);

        return view('posts.category', [
            'category' => $category,
            'posts' => $posts,
            'language' => $language,
        ]);
    }

    private function renderShow(string $language, string $categorySlug, string $slug): View|RedirectResponse
    {
        $post = Post::published()
            ->with('category')
            ->language($language)
            ->where('slug', $slug)
            ->firstOrFail();

        // Canonicalise the category segment: a stale or wrong category in the URL
        // 301s to the post's real URL (also covers a post being recategorised).
        if ($categorySlug !== ($post->category?->slug ?? 'uncategorized')) {
            return redirect()->to($post->url(), 301);
        }

        return view('posts.show', [
            'post' => $post,
            'related' => $post->relatedPosts(),
            'versions' => $post->languageVersions(),
        ]);
    }

    /**
     * Resolve a category slug to a model, or a transient "uncategorized"
     * placeholder so listing views can render without a real category.
     */
    private function resolveCategory(string $categorySlug): ServiceCategory
    {
        if ($categorySlug === 'uncategorized') {
            return new ServiceCategory(['name' => 'Uncategorized', 'slug' => 'uncategorized']);
        }

        return ServiceCategory::where('slug', $categorySlug)->firstOrFail();
    }

    private function localeExists(string $locale): bool
    {
        return Post::query()->where('language', $locale)->exists();
    }
}
