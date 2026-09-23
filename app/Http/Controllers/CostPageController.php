<?php

namespace App\Http\Controllers;

use App\Models\CostPage;
use App\Support\Locale;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CostPageController extends Controller
{
    /** Pricing page, default language: /pricing/{slug} */
    public function show(Request $request, string $slug): Response
    {
        return $this->render($request, Locale::DEFAULT, $slug);
    }

    /** Localized pricing page: /{locale}/pricing/{slug} */
    public function showLocalized(Request $request, string $locale, string $slug): Response
    {
        abort_unless(Locale::isSupported($locale), 404);

        return $this->render($request, $locale, $slug);
    }

    private function render(Request $request, string $language, string $slug): Response
    {
        $page = CostPage::published()
            ->with(['category', 'sections.items', 'zones', 'presets', 'countries', 'techniques', 'tiers.prices', 'features.values', 'comparisonRows', 'procedureSteps', 'recoveryStages', 'recoveryPhases'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Unlisted pages with a key are a 404 without it, so a leaked bare URL shows nothing.
        abort_unless($page->acceptsKey($request->query('key')), 404);

        $response = response()->view('cost.show', [
            'page' => $page,
            'language' => $language,
            'sections' => $page->visibleSections(),
        ]);

        if ($page->is_unlisted) {
            // Belt and braces with the <meta name="robots"> tag: search and AI crawlers stay out.
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive, noai, noimageai');
            $response->headers->set('Cache-Control', 'private, no-store');
        }

        return $response;
    }
}
