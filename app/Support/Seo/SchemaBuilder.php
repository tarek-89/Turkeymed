<?php

namespace App\Support\Seo;

use App\Models\Author;
use App\Models\Office;
use App\Models\Post;
use App\Models\Service;
use App\Models\Setting;
use App\Models\SocialLink;
use App\Models\Testimonial;

/**
 * Builds the sitewide JSON-LD entity graph (MedicalOrganization + WebSite).
 *
 * Pure data composition — no view logic — so it can be unit-tested. Empty
 * fields are stripped so the markup never advertises blank values, and the
 * two entities cross-reference by @id for clean entity resolution.
 */
class SchemaBuilder
{
    /**
     * The full sitewide graph for the given locale.
     *
     * @return array<string, mixed>
     */
    public static function siteGraph(?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                self::organization($locale),
                self::website($locale),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function organization(?string $locale = null): array
    {
        $locale ??= app()->getLocale();
        $home = url('/');

        return array_filter([
            '@type' => 'MedicalOrganization',
            '@id' => $home.'#organization',
            'name' => (string) config('site.brand'),
            'legalName' => self::string(Setting::get('org.legal_name')),
            'url' => $home,
            'logo' => self::logoUrl(),
            'image' => self::logoUrl(),
            'telephone' => self::string(config('site.phone')),
            'email' => self::string(config('site.email')),
            'foundingDate' => self::string(Setting::get('org.founding_date')),
            'areaServed' => self::string(Setting::get('org.area_served')),
            'address' => self::address($locale),
            'sameAs' => self::sameAs(),
            'medicalSpecialty' => self::stringList(Setting::get('org.medical_specialties', [])),
            'hasCredential' => self::credentials(),
            'aggregateRating' => self::aggregateRating(),
        ], static fn ($value): bool => $value !== null && $value !== '' && $value !== []);
    }

    /**
     * FAQPage built from a list of question/answer pairs, or null when empty.
     *
     * @param  list<array{question: string, answer: string}>  $faqs
     * @return array<string, mixed>|null
     */
    public static function faqPage(array $faqs): ?array
    {
        if ($faqs === []) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(static fn (array $faq): array => [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']],
            ], $faqs),
        ];
    }

    /**
     * AggregateRating from published, rated testimonials, or null when none.
     *
     * Note: Google does not render star snippets for an organization's reviews
     * about itself — this is for entity/AI understanding, not SERP stars.
     *
     * @return array<string, mixed>|null
     */
    private static function aggregateRating(): ?array
    {
        $ratings = Testimonial::query()
            ->where('is_published', true)
            ->whereNotNull('rating')
            ->pluck('rating');

        if ($ratings->isEmpty()) {
            return null;
        }

        return [
            '@type' => 'AggregateRating',
            'ratingValue' => round((float) $ratings->avg(), 1),
            'reviewCount' => $ratings->count(),
            'bestRating' => 5,
            'worstRating' => 1,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function website(?string $locale = null): array
    {
        $locale ??= app()->getLocale();
        $home = url('/');

        return [
            '@type' => 'WebSite',
            '@id' => $home.'#website',
            'url' => $home,
            'name' => (string) config('site.brand'),
            'inLanguage' => $locale,
            'publisher' => ['@id' => $home.'#organization'],
        ];
    }

    /**
     * A Person entity for an author/reviewer, with credentials and profiles.
     *
     * @return array<string, mixed>
     */
    public static function person(Author $author, ?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        $jobTitle = trim(implode(', ', array_filter([$author->title, $author->credentials])));

        return array_filter([
            '@type' => 'Person',
            'name' => $author->name,
            'jobTitle' => $jobTitle !== '' ? $jobTitle : null,
            'description' => $author->translate('bio', $locale),
            'url' => $author->url($locale),
            'image' => $author->photoUrl(),
            'sameAs' => $author->profileLinks(),
        ], static fn ($value): bool => $value !== null && $value !== '' && $value !== []);
    }

    /**
     * BlogPosting for an article, with full author/reviewer Person entities
     * and a reference to the sitewide organization as publisher.
     *
     * @return array<string, mixed>
     */
    public static function blogPosting(Post $post, ?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $post->title,
            'inLanguage' => $post->language,
            'mainEntityOfPage' => $post->url(),
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => $post->updated_at?->toIso8601String(),
            'author' => self::authorEntity($post->authorProfile, $post->author, $locale),
            'reviewedBy' => $post->reviewer ? self::person($post->reviewer, $locale) : null,
            'publisher' => ['@id' => url('/').'#organization'],
            'image' => $post->featuredImageUrl(),
            'description' => $post->metaDescription(),
        ], static fn ($value): bool => $value !== null && $value !== '' && $value !== []);
    }

    /**
     * MedicalWebPage for a service, wrapping the MedicalProcedure and carrying
     * the trust signals Google expects on medical pages.
     *
     * @return array<string, mixed>
     */
    public static function medicalWebPage(Service $service, ?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        $procedure = array_filter([
            '@type' => 'MedicalProcedure',
            'name' => $service->title,
            'description' => $service->metaDescription(),
        ], static fn ($value): bool => $value !== null && $value !== '');

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'MedicalWebPage',
            'name' => $service->title,
            'inLanguage' => $service->language,
            'url' => $service->url(),
            'description' => $service->metaDescription(),
            'image' => $service->featuredImageUrl(),
            'datePublished' => $service->published_at?->toIso8601String(),
            'dateModified' => $service->updated_at?->toIso8601String(),
            'lastReviewed' => $service->last_reviewed_at?->toIso8601String(),
            'reviewedBy' => $service->reviewer ? self::person($service->reviewer, $locale) : null,
            'author' => self::authorEntity($service->authorProfile, $service->author, $locale),
            'about' => $procedure,
            'publisher' => ['@id' => url('/').'#organization'],
        ], static fn ($value): bool => $value !== null && $value !== '' && $value !== []);
    }

    /**
     * Prefer the credentialed Author entity; fall back to the legacy name string.
     *
     * @return array<string, mixed>|null
     */
    private static function authorEntity(?Author $author, ?string $legacyName, string $locale): ?array
    {
        if ($author) {
            return self::person($author, $locale);
        }

        return ($legacyName !== null && trim($legacyName) !== '')
            ? ['@type' => 'Person', 'name' => $legacyName]
            : null;
    }

    private static function logoUrl(): string
    {
        $logo = Setting::get('org.logo');

        if (is_string($logo) && $logo !== '') {
            return rtrim((string) config('filesystems.disks.r2.url'), '/').'/'.ltrim($logo, '/');
        }

        return url('/icon-512.png');
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function address(string $locale): ?array
    {
        $office = Office::query()->where('is_primary', true)->first()
            ?? Office::query()->where('is_published', true)->orderBy('sort_order')->first();

        if ($office === null) {
            return null;
        }

        $address = array_filter([
            '@type' => 'PostalAddress',
            'streetAddress' => $office->translate('address', $locale),
            'addressLocality' => $office->translate('name', $locale),
            'addressCountry' => $office->translate('country', $locale),
        ], static fn ($value): bool => $value !== null && $value !== '');

        // Only the @type means we have nothing useful to say.
        return count($address) > 1 ? $address : null;
    }

    /**
     * Published social profiles, for the organization's sameAs.
     *
     * @return list<string>
     */
    private static function sameAs(): array
    {
        return SocialLink::query()
            ->where('is_published', true)
            ->whereNotNull('url')
            ->orderBy('sort_order')
            ->pluck('url')
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, string>>
     */
    private static function credentials(): array
    {
        return array_map(
            static fn (string $name): array => ['@type' => 'EducationalOccupationalCredential', 'name' => $name],
            self::stringList(Setting::get('org.accreditations', [])),
        );
    }

    private static function string(mixed $value): ?string
    {
        return (is_string($value) && trim($value) !== '') ? $value : null;
    }

    /**
     * @return list<string>
     */
    private static function stringList(mixed $value): array
    {
        return collect((array) $value)
            ->filter(static fn ($item): bool => is_string($item) && trim($item) !== '')
            ->values()
            ->all();
    }
}
