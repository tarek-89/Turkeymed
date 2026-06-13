<?php

namespace App\Http\Controllers;

use App\Models\Promise;
use App\Models\Setting;
use App\Models\Stat;
use App\Support\Locale;
use Illuminate\View\View;

class AboutController extends Controller
{
    /** About page, default language: /about */
    public function show(): View
    {
        return $this->render(Locale::DEFAULT);
    }

    /** Localized about page: /{locale}/about */
    public function showLocalized(string $locale): View
    {
        abort_unless(Locale::isSupported($locale), 404);

        return $this->render($locale);
    }

    private function render(string $language): View
    {
        $r2 = rtrim((string) config('filesystems.disks.r2.url'), '/');

        return view('about.show', [
            'language' => $language,
            'heading' => Setting::translated('about.heading', $language),
            'text' => Setting::translated('about.text', $language),
            'images' => array_map(
                fn (string $path): string => $r2.'/'.ltrim($path, '/'),
                (array) Setting::get('about.images', []),
            ),
            'storyTitle' => Setting::translated('about.story_title', $language),
            'storyText' => Setting::translated('about.story_text', $language),
            'promises' => Promise::published()->orderBy('sort_order')->get(),
            'stats' => Stat::published()->orderBy('sort_order')->get(),
        ]);
    }
}
