<?php

namespace App\Support;

class Navigation
{
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
            ['label' => __('nav.treatments'), 'url' => '#'],
            ['label' => __('nav.about'), 'url' => self::aboutUrl()],
            ['label' => __('nav.results'), 'url' => '#'],
            ['label' => __('nav.blog'), 'url' => self::blogUrl()],
            ['label' => __('nav.contact'), 'url' => self::contactUrl()],
        ]);
    }

    /**
     * Footer "Treatments" column.
     *
     * @return list<array{label: string, url: string}>
     */
    public static function footerTreatments(): array
    {
        return [
            ['label' => __('footer.links.hair_transplant'), 'url' => '#'],
            ['label' => __('footer.links.dental'), 'url' => '#'],
            ['label' => __('footer.links.eye_surgery'), 'url' => '#'],
            ['label' => __('footer.links.packages'), 'url' => '#'],
        ];
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
            ['label' => __('footer.links.results'), 'url' => '#'],
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
