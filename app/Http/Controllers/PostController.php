<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Service;
use Illuminate\View\View;

class PostController extends Controller
{
    /** Blog index, default language (en): /blog */
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

    /**
     * Catch-all for English slugs: /{slug}
     * Tries posts first, then services, then 404s.
     */
    public function show(string $slug): View
    {
        return $this->renderPostOrService(Post::DEFAULT_LANGUAGE, $slug);
    }

    /**
     * Catch-all for localized slugs: /{locale}/{slug}
     * Tries posts first, then services, then 404s.
     */
    public function showLocalized(string $locale, string $slug): View
    {
        return $this->renderPostOrService($locale, $slug);
    }

    private function renderIndex(string $language): View
    {
        $posts = Post::published()
            ->language($language)
            ->orderByDesc('published_at')
            ->paginate(12);

        return view('posts.index', [
            'posts' => $posts,
            'language' => $language,
        ]);
    }

    private function renderPostOrService(string $language, string $slug): View
    {
        $post = Post::published()
            ->language($language)
            ->where('slug', $slug)
            ->first();

        if ($post) {
            return view('posts.show', [
                'post' => $post,
                'related' => $post->relatedPosts(),
                'versions' => $post->languageVersions(),
            ]);
        }

        // No post matched — try services
        $service = Service::published()
            ->language($language)
            ->where('slug', $slug)
            ->firstOrFail();

        return view('services.show', [
            'service' => $service,
            'related' => $service->relatedServices(),
            'results' => $service->patientResults(),
            'versions' => $service->languageVersions(),
        ]);
    }

    private function localeExists(string $locale): bool
    {
        return Post::query()->where('language', $locale)->exists()
            || Service::query()->where('language', $locale)->exists();
    }
}
