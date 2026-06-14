<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServiceController extends Controller
{
    /** Services index (all categories): /services */
    public function index(): View
    {
        return $this->renderIndex(Post::DEFAULT_LANGUAGE);
    }

    /** Localized services index: /{locale}/services */
    public function indexLocalized(string $locale): View
    {
        abort_unless($this->localeExists($locale), 404);

        return $this->renderIndex($locale);
    }

    /** Service category listing: /services/{categorySlug} */
    public function category(string $categorySlug): View
    {
        return $this->renderCategory(Post::DEFAULT_LANGUAGE, $categorySlug);
    }

    /** Localized service category listing: /{locale}/services/{categorySlug} */
    public function categoryLocalized(string $locale, string $categorySlug): View
    {
        abort_unless($this->localeExists($locale), 404);

        return $this->renderCategory($locale, $categorySlug);
    }

    /** Service page: /services/{categorySlug}/{slug} */
    public function show(string $categorySlug, string $slug): View|RedirectResponse
    {
        return $this->renderShow(Post::DEFAULT_LANGUAGE, $categorySlug, $slug);
    }

    /** Localized service page: /{locale}/services/{categorySlug}/{slug} */
    public function showLocalized(string $locale, string $categorySlug, string $slug): View|RedirectResponse
    {
        return $this->renderShow($locale, $categorySlug, $slug);
    }

    private function renderIndex(string $language): View
    {
        $categories = ServiceCategory::query()
            ->withCount(['services' => fn ($query) => $query->published()->language($language)])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->filter(fn (ServiceCategory $category): bool => $category->services_count > 0)
            ->values();

        return view('services.index', [
            'categories' => $categories,
            'language' => $language,
        ]);
    }

    private function renderCategory(string $language, string $categorySlug): View
    {
        $category = $this->resolveCategory($categorySlug);

        $services = Service::published()
            ->with('category')
            ->language($language)
            ->when(
                $category->exists,
                fn ($query) => $query->where('service_category_id', $category->id),
                fn ($query) => $query->whereNull('service_category_id'),
            )
            ->orderBy('title')
            ->get();

        $posts = Post::published()
            ->with('category')
            ->language($language)
            ->when(
                $category->exists,
                fn ($query) => $query->where('service_category_id', $category->id),
                fn ($query) => $query->whereNull('service_category_id'),
            )
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

    private function renderShow(string $language, string $categorySlug, string $slug): View|RedirectResponse
    {
        $service = Service::published()
            ->with('category')
            ->language($language)
            ->where('slug', $slug)
            ->firstOrFail();

        // Canonicalise the category segment (covers recategorised services).
        if ($categorySlug !== ($service->category?->slug ?? 'uncategorized')) {
            return redirect()->to($service->url(), 301);
        }

        return view('services.show', [
            'service' => $service,
            'related' => $service->relatedServices(),
            'results' => $service->patientResults(),
            'versions' => $service->languageVersions(),
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
        return Post::query()->where('language', $locale)->exists()
            || Service::query()->where('language', $locale)->exists();
    }
}
