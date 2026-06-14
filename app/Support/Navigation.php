<?php

namespace App\Support;

use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Navigation
{
    /**
     * Treatment mega-menu: each service category that has at least one
     * published service in the current locale, with its services as a
     * dropdown. Eager-loaded to avoid N+1; categories without published
     * services are omitted.
     *
     * @return list<array{label: string, url: string, services: list<array{label: string, url: string}>}>
     */
    public static function treatmentMenu(): array
    {
        $locale = app()->getLocale();

        return ServiceCategory::query()
            ->with(['services' => fn (HasMany $query) => $query
                ->where('status', 'publish')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->where('language', $locale)
                ->orderBy('title')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (ServiceCategory $category): array => [
                'label' => $category->name,
                'url' => $category->url($locale),
                'services' => $category->services
                    ->map(fn ($service): array => [
                        'label' => $service->title,
                        'url' => $service->url(),
                    ])->all(),
            ])
            ->filter(fn (array $category): bool => $category['services'] !== [])
            ->values()
            ->all();
    }

    /**
     * URL of the localized homepage (root for English, /{code} otherwise).
     */
    public static function homeUrl(): string
    {
        $locale = app()->getLocale();

        return url($locale === Locale::DEFAULT ? '/' : '/'.$locale);
    }

    /**
     * URL of the localized contact page.
     */
    public static function contactUrl(): string
    {
        $locale = app()->getLocale();

        return $locale === Locale::DEFAULT
            ? route('contact')
            : route('contact.localized', $locale);
    }

    /**
     * URL of the localized about page.
     */
    public static function aboutUrl(): string
    {
        $locale = app()->getLocale();

        return $locale === Locale::DEFAULT
            ? route('about')
            : route('about.localized', $locale);
    }

    /**
     * URL of the localized blog index.
     */
    public static function blogUrl(): string
    {
        $locale = app()->getLocale();

        return $locale === Locale::DEFAULT
            ? route('posts.index')
            : route('posts.index.localized', $locale);
    }

    /**
     * URL of the localized services index.
     */
    public static function servicesUrl(): string
    {
        $locale = app()->getLocale();

        return $locale === Locale::DEFAULT
            ? route('services.index')
            : route('services.index.localized', $locale);
    }

    /**
     * Primary header / drawer navigation.
     *
     * TODO: replace the '#' placeholders with route() calls as each section
     * (treatments, results, blog, about, contact) is built.
     *
     * @return list<array{label: string, url: string, active: bool}>
     */
    public static function primary(): array
    {
        return self::withActiveState([
            ['label' => __('nav.about'), 'url' => self::aboutUrl()],
            ['label' => __('nav.blog'), 'url' => self::blogUrl()],
            ['label' => __('nav.contact'), 'url' => self::contactUrl()],
        ]);
    }

    /**
     * Footer "Treatments" column — the live service categories that have at
     * least one published service in the current locale, linking to their
     * category pages. Falls back to nothing (column hides) when empty.
     *
     * @return list<array{label: string, url: string}>
     */
    public static function footerTreatments(): array
    {
        return collect(self::treatmentMenu())
            ->map(fn (array $category): array => [
                'label' => $category['label'],
                'url' => $category['url'],
            ])
            ->all();
    }

    /**
     * Footer "Company" column.
     *
     * @return list<array{label: string, url: string}>
     */
    public static function footerCompany(): array
    {
        return [
            ['label' => __('footer.links.about_us'), 'url' => self::aboutUrl()],
            ['label' => __('footer.links.blog'), 'url' => self::blogUrl()],
            ['label' => __('footer.links.contact'), 'url' => self::contactUrl()],
        ];
    }

    /**
     * Mark each item active when it matches the current URL.
     *
     * @param  list<array{label: string, url: string}>  $items
     * @return list<array{label: string, url: string, active: bool}>
     */
    private static function withActiveState(array $items): array
    {
        return array_map(static function (array $item): array {
            $item['active'] = $item['url'] !== '#' && request()->fullUrlIs($item['url'].'*');

            return $item;
        }, $items);
    }
}
