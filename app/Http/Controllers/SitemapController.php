<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * The XML sitemap: static pages, every published post and service (with
     * hreflang alternates for translated content) and the service categories.
     */
    public function index(): Response
    {
        $urls = array_merge(
            $this->staticEntries(),
            $this->contentEntries(Post::published()->with('category')->get()),
            $this->contentEntries(Service::published()->with('category')->get()),
            $this->categoryEntries(),
        );

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Homepage, blog index, about and contact — one entry per locale, each
     * cross-linked to its siblings with hreflang alternates.
     *
     * @return list<array{loc: string, lastmod: ?string, alternates: array<string, string>}>
     */
    private function staticEntries(): array
    {
        $pages = [
            fn (string $code): string => $code === Post::DEFAULT_LANGUAGE ? url('/') : url('/'.$code),
            fn (string $code): string => $code === Post::DEFAULT_LANGUAGE ? url('/blog') : url('/'.$code.'/blog'),
            fn (string $code): string => $code === Post::DEFAULT_LANGUAGE ? url('/services') : url('/'.$code.'/services'),
            fn (string $code): string => $code === Post::DEFAULT_LANGUAGE ? url('/about') : url('/'.$code.'/about'),
            fn (string $code): string => $code === Post::DEFAULT_LANGUAGE ? url('/contact') : url('/'.$code.'/contact'),
        ];

        $codes = Locale::codes();
        $entries = [];

        foreach ($pages as $urlFor) {
            $alternates = [];
            foreach ($codes as $code) {
                $alternates[$code] = $urlFor($code);
            }

            foreach ($codes as $code) {
                $entries[] = [
                    'loc' => $alternates[$code],
                    'lastmod' => null,
                    'alternates' => $alternates,
                ];
            }
        }

        return $entries;
    }

    /**
     * Posts or services grouped by translation group so every language version
     * advertises the others via hreflang.
     *
     * @param  Collection<int, Post|Service>  $items
     * @return list<array{loc: string, lastmod: ?string, alternates: array<string, string>}>
     */
    private function contentEntries(Collection $items): array
    {
        $entries = [];

        foreach ($items->whereNotNull('translation_group_id')->groupBy('translation_group_id') as $group) {
            $alternates = $group->mapWithKeys(fn ($item): array => [$item->language => $item->url()])->all();

            foreach ($group as $item) {
                $entries[] = [
                    'loc' => $item->url(),
                    'lastmod' => $item->updated_at?->toAtomString(),
                    'alternates' => $alternates,
                    'image' => $item->featuredImageUrl(),
                ];
            }
        }

        foreach ($items->whereNull('translation_group_id') as $item) {
            $entries[] = [
                'loc' => $item->url(),
                'lastmod' => $item->updated_at?->toAtomString(),
                'alternates' => [],
                'image' => $item->featuredImageUrl(),
            ];
        }

        return $entries;
    }

    /**
     * @return list<array{loc: string, lastmod: ?string, alternates: array<string, string>}>
     */
    private function categoryEntries(): array
    {
        $entries = [];

        foreach (ServiceCategory::query()->orderBy('sort_order')->get() as $category) {
            foreach ([$category->serviceUrl(), $category->blogUrl()] as $loc) {
                $entries[] = [
                    'loc' => $loc,
                    'lastmod' => $category->updated_at?->toAtomString(),
                    'alternates' => [],
                ];
            }
        }

        return $entries;
    }
}
