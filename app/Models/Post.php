<?php

namespace App\Models;

use App\Observers\FeaturedImageObserver;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([FeaturedImageObserver::class])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    protected $guarded = [];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_elementor' => 'boolean',
            'published_at' => 'datetime',
            'wp_modified_at' => 'datetime',
            'featured_image_meta' => 'array',
            'faqs' => 'array',
        ];
    }

    /* ---------------- Relationships ---------------- */

    /** @return BelongsTo<ServiceCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    /** The user credited as author (preferred over the legacy `author` string). @return BelongsTo<User, $this> */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /* ---------------- Scopes ---------------- */

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'publish')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeLanguage(Builder $query, string $language): Builder
    {
        return $query->where('language', $language);
    }

    /* ---------------- Translations ---------------- */

    /**
     * Sibling translations of this post (other languages in the same group).
     * Used for the language switcher and hreflang tags.
     */
    public function translations(): Collection
    {
        if ($this->translation_group_id === null) {
            return new Collection;
        }

        return static::query()
            ->where('translation_group_id', $this->translation_group_id)
            ->where('id', '!=', $this->id)
            ->get();
    }

    /** All language versions including this one, keyed by language code. */
    public function languageVersions(): Collection
    {
        if ($this->translation_group_id === null) {
            return new Collection([$this->language => $this]);
        }

        return static::query()
            ->where('translation_group_id', $this->translation_group_id)
            ->get()
            ->keyBy('language');
    }

    /* ---------------- Related content ---------------- */

    /**
     * Posts from the same category (randomized so visitors discover
     * something new on every page load). Empty without a category.
     */
    public function relatedPosts(int $limit = 3): Collection
    {
        if (! $this->service_category_id) {
            return new Collection;
        }

        return static::published()
            ->language($this->language)
            ->where('service_category_id', $this->service_category_id)
            ->where('id', '!=', $this->id)
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    /**
     * Estimated reading time in minutes (unicode-safe, ~200 words per minute).
     */
    public function readingTime(): int
    {
        $words = preg_split('/\s+/u', trim(strip_tags($this->body ?? '')), -1, PREG_SPLIT_NO_EMPTY);

        return max(1, (int) ceil(count($words) / 200));
    }

    /* ---------------- URLs & SEO ---------------- */

    public const DEFAULT_LANGUAGE = 'en';

    /** @var array<string, string> */
    public const LANGUAGES = [
        'en' => 'English',
        'ar' => 'العربية (Arabic)',
        'fr' => 'Français',
        'es' => 'Español',
        'it' => 'Italiano',
        'de' => 'Deutsch',
        'el' => 'Ελληνικά (Greek)',
        'pl' => 'Polski',
        'pt' => 'Português',
        'nl' => 'Nederlands',
        'no' => 'Norsk',
        'da' => 'Dansk',
        'sv' => 'Svenska',
        'tr' => 'Türkçe',
        'ru' => 'Русский',
    ];

    /**
     * Known languages merged with whatever exists in the database,
     * so imported languages never go missing from admin selects.
     *
     * @return array<string, string>
     */
    public static function languageOptions(): array
    {
        $options = self::LANGUAGES;

        foreach (self::query()->distinct()->pluck('language') as $code) {
            $options[$code] ??= strtoupper($code);
        }

        return $options;
    }

    /** Public URL: /blog/{category}/{slug} (uncategorized posts use the "uncategorized" segment). */
    public function url(): string
    {
        $category = $this->category?->slug ?? 'uncategorized';

        return $this->language === self::DEFAULT_LANGUAGE
            ? route('posts.show', [$category, $this->slug])
            : route('posts.show.localized', [$this->language, $category, $this->slug]);
    }

    /** Public URL of the featured image (stored as a relative path like "2025/02/photo.jpg"). */
    public function featuredImageUrl(): ?string
    {
        if (! $this->featured_image) {
            return null;
        }

        return rtrim((string) config('filesystems.disks.r2.url'), '/').'/'.ltrim($this->featured_image, '/');
    }

    /** Responsive WebP srcset built from generated variants, or null when there are none. */
    public function featuredImageSrcset(): ?string
    {
        $variants = $this->featured_image_meta['variants'] ?? [];

        if (! is_array($variants) || $variants === []) {
            return null;
        }

        $base = rtrim((string) config('filesystems.disks.r2.url'), '/');

        return collect($variants)
            ->map(fn (string $path, int|string $width): string => $base.'/'.ltrim($path, '/').' '.$width.'w')
            ->values()
            ->implode(', ');
    }

    /** Meta title: Rank Math override (used as-is), or the default template trimmed to ~60 chars for SERPs. */
    public function metaTitle(): string
    {
        return $this->meta_title ?: str($this->title.' - '.config('app.name'))->limit(60, '')->toString();
    }

    public function metaDescription(): ?string
    {
        if ($this->meta_description) {
            return $this->meta_description;
        }

        $fallback = $this->summary ?: $this->excerpt;

        return $fallback ? str($fallback)->limit(155)->toString() : null;
    }

    /**
     * Cleaned list of FAQ entries (drops blanks).
     *
     * @return list<array{question: string, answer: string}>
     */
    public function faqList(): array
    {
        return collect($this->faqs ?? [])
            ->filter(fn ($faq): bool => is_array($faq) && filled($faq['question'] ?? null) && filled($faq['answer'] ?? null))
            ->map(fn (array $faq): array => ['question' => (string) $faq['question'], 'answer' => (string) $faq['answer']])
            ->values()
            ->all();
    }

    /**
     * Services in the same category — cross-links a blog post to treatments.
     *
     * @return Collection<int, Service>
     */
    public function relatedServices(int $limit = 3): Collection
    {
        if (! $this->service_category_id) {
            return new Collection;
        }

        return Service::published()
            ->with('category')
            ->language($this->language)
            ->where('service_category_id', $this->service_category_id)
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }
}
