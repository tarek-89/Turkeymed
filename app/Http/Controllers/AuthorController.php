<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Post;
use App\Models\Service;
use App\Support\Locale;
use Illuminate\View\View;

class AuthorController extends Controller
{
    /** Author profile, default language: /authors/{slug} */
    public function show(string $authorSlug): View
    {
        return $this->render(Post::DEFAULT_LANGUAGE, $authorSlug);
    }

    /** Localized author profile: /{locale}/authors/{slug} */
    public function showLocalized(string $locale, string $authorSlug): View
    {
        abort_unless(Locale::isSupported($locale), 404);

        return $this->render($locale, $authorSlug);
    }

    private function render(string $language, string $authorSlug): View
    {
        $author = Author::published()->where('slug', $authorSlug)->firstOrFail();

        $posts = Post::published()
            ->with('category')
            ->language($language)
            ->where('author_id', $author->id)
            ->orderByDesc('published_at')
            ->get();

        $services = Service::published()
            ->with('category')
            ->language($language)
            ->where('author_id', $author->id)
            ->orderBy('title')
            ->get();

        return view('authors.show', [
            'author' => $author,
            'posts' => $posts,
            'services' => $services,
            'language' => $language,
        ]);
    }
}
