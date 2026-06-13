<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\InstagramPost;
use App\Models\PatientResult;
use App\Models\Post;
use App\Models\ProcessStep;
use App\Models\Setting;
use App\Models\Stat;
use App\Models\Testimonial;
use App\Models\TreatmentCard;
use App\Models\Video;
use App\Support\Locale;
use Illuminate\View\View;

class HomeController extends Controller
{
    /** Marketing homepage, default language: / */
    public function index(): View
    {
        return $this->render(Locale::DEFAULT);
    }

    /** Localized homepage: /{locale} */
    public function indexLocalized(string $locale): View
    {
        abort_unless(Locale::isSupported($locale), 404);

        return $this->render($locale);
    }

    private function render(string $language): View
    {
        $r2 = rtrim((string) config('filesystems.disks.r2.url'), '/');

        return view('home.index', [
            'language' => $language,

            // Hero (managed via Filament → Pages → Homepage)
            'heroBadge' => Setting::translated('home.hero_badge', $language),
            'heroTitle' => Setting::translated('home.hero_title', $language),
            'heroTitleAccent' => Setting::translated('home.hero_title_accent', $language),
            'heroLead' => Setting::translated('home.hero_lead', $language),
            'heroImages' => array_map(
                fn (string $path): string => $r2.'/'.ltrim($path, '/'),
                (array) Setting::get('home.hero_images', []),
            ),
            'heroStatValue' => Setting::get('home.hero_stat_value'),
            'heroStatLabel' => Setting::translated('home.hero_stat_label', $language),

            // Bottom CTA
            'ctaTitle' => Setting::translated('home.cta_title', $language),
            'ctaText' => Setting::translated('home.cta_text', $language),

            // Component sections
            'treatments' => TreatmentCard::published()->orderBy('sort_order')->get(),
            'stats' => Stat::published()->orderBy('sort_order')->get(),
            'results' => PatientResult::published()->orderBy('sort_order')->limit(4)->get(),
            'testimonials' => Testimonial::published()->orderBy('sort_order')->get(),
            'steps' => ProcessStep::published()->orderBy('sort_order')->get(),
            'galleries' => Gallery::published()->orderBy('sort_order')->get(),
            'videos' => Video::published()->orderBy('sort_order')->get(),
            'instagramPosts' => InstagramPost::published()->orderBy('sort_order')->get(),
            'posts' => Post::published()->language($language)->orderByDesc('published_at')->limit(3)->get(),
        ]);
    }
}
