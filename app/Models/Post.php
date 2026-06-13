<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    /** @use HasFactory<\Database\Factories\PostFactory> */
    use HasFactory;

    protected $guarded = [];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_elementor' => 'boolean',
            'published_at' => 'datetime',
            'wp_modified_at' => 'datetime',
        ];
    }

    /* ---------------- Relationships ---------------- */

    /** @return BelongsTo<ServiceCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
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
            return new Collection();
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
            return new Collection();
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

    /** Public URL, preserving the WordPress scheme: no prefix for default language. */
    public function url(): string
    {
        return $this->language === self::DEFAULT_LANGUAGE
            ? url('/'.$this->slug)
            : url('/'.$this->language.'/'.$this->slug);
    }

    /** Public URL of the featured image (stored as a relative path like "2025/02/photo.jpg"). */
    public function featuredImageUrl(): ?string
    {
        if (! $this->featured_image) {
            return null;
        }

        return rtrim((string) config('filesystems.disks.r2.url'), '/').'/'.ltrim($this->featured_image, '/');
    }

    /** Meta title: Rank Math override, or the default template (matches Rank Math's "%title% - %sitename%"). */
    public function metaTitle(): string
    {
        return $this->meta_title ?: $this->title.' - '.config('app.name');
    }

    public function metaDescription(): ?string
    {
        return $this->meta_description ?: ($this->excerpt ? str($this->excerpt)->limit(155)->toString() : null);
    }
}
