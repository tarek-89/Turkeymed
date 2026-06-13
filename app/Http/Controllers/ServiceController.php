<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\View\View;

class ServiceController extends Controller
{
    /** Category listing (all services in a category): /category/{categorySlug} */
    public function category(string $categorySlug): View
    {
        return $this->renderCategory(Post::DEFAULT_LANGUAGE, $categorySlug);
    }

    /** Localized category listing: /{locale}/category/{categorySlug} */
    public function categoryLocalized(string $locale, string $categorySlug): View
    {
        return $this->renderCategory($locale, $categorySlug);
    }

    private function renderCategory(string $language, string $categorySlug): View
    {
        $category = ServiceCategory::where('slug', $categorySlug)->firstOrFail();

        $services = Service::published()
            ->language($language)
            ->where('service_category_id', $category->id)
            ->orderBy('title')
            ->get();

        $posts = Post::published()
            ->language($language)
            ->where('service_category_id', $category->id)
            ->orderByDesc('published_at')
            ->limit(6)
            ->get();

        return view('services.category', [
            'category' => $category,
            'services' => $services,
            'posts' => $posts,
            'language' => $language,
        ]);
    }
}
