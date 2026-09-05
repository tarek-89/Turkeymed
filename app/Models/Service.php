<?php

namespace App\Models;

use App\Observers\FeaturedImageObserver;
use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([FeaturedImageObserver::class])]
class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory;

    protected $guarded = [];

    protected static function booted(): void
    {
        // Sort order is set once per treatment, not per language: when a row's
        // position changes, mirror it to its translations so every locale's
        // menu and category page share the same order.
        static::saved(function (self $service): void {
            if ($service->wasChanged('sort_order')) {
                $service->syncSortOrderToTranslations();
            }
        });
    }

    /**
     * Move this service one position up (-1) or down (+1) within its category
     * and language, then renumber the whole group 1..n so positions stay
     * unique. Each change is mirrored to the row's other-language translations.
     */
    public function moveBy(int $direction): void
    {
        $siblings = static::query()
            ->where('language', $this->language)
            ->where('service_category_id', $this->service_category_id)
            ->ordered()
            ->get()
            ->values();

        $index = $siblings->search(fn (self $service): bool => $service->is($this));
        $target = $index + $direction;

        if ($index === false || $target < 0 || $target >= $siblings->count()) {
            return;
        }

        $reordered = $siblings->all();
        [$reordered[$index], $reordered[$target]] = [$reordered[$target], $reordered[$index]];

        foreach ($reordered as $position => $service) {
            $service->update(['sort_order' => $position + 1]);
        }
    }

    /**
     * Copy this row's sort_order to its sibling translations. Also called after
     * Filament drag-reordering, which bulk-updates without firing model events.
     */
    public function syncSortOrderToTranslations(): void
    {
        if ($this->translation_group_id === null) {
            return;
        }

        static::query()
            ->where('translation_group_id', $this->translation_group_id)
            ->where('id', '!=', $this->id)
            ->update(['sort_order' => $this->sort_order]);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_elementor' => 'boolean',
            'sort_order' => 'integer',
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

    /**
     * Manual admin order (drag-sorted in Filament), alphabetical as tiebreaker.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }

    /* ---------------- Translations ---------------- */

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

    /* ---------------- URLs & SEO ---------------- */

    /** Public URL: /services/{category}/{slug} (uncategorized services use the "uncategorized" segment). */
    public function url(): string
    {
        $category = $this->category?->slug ?? 'uncategorized';

        return $this->language === Post::DEFAULT_LANGUAGE
            ? route('services.show', [$category, $this->slug])
            : route('services.show.localized', [$this->language, $category, $this->slug]);
    }

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

    public function metaTitle(): string
    {
        return $this->meta_title ?: str($this->title.' - '.config('app.name'))->limit(60, '')->toString();
    }

    public function metaDescription(): ?string
    {
        if ($this->meta_description) {
            return $this->meta_description;
        }

        $fallback = $this->summary ?: $this->excerpt ?: Post::descriptionFromBody($this->body);

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
     * Blog posts in the same category — cross-links a treatment to articles.
     *
     * @return Collection<int, Post>
     */
    public function relatedPosts(int $limit = 3): Collection
    {
        if (! $this->service_category_id) {
            return new Collection;
        }

        return Post::published()
            ->with('category')
            ->language($this->language)
            ->where('service_category_id', $this->service_category_id)
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Published patient results for this page: results pinned to this service
     * plus shared results from its category, in manual sort order.
     */
    public function patientResults(int $limit = 10): Collection
    {
        return PatientResult::published()
            ->where(function (Builder $query): void {
                $query->where('service_id', $this->id);

                if ($this->service_category_id) {
                    $query->orWhere(function (Builder $inner): void {
                        $inner->whereNull('service_id')
                            ->where('service_category_id', $this->service_category_id);
                    });
                }
            })
            ->orderBy('sort_order')
            ->limit($limit)
            ->get();
    }

    /**
     * Other services in the same category (for sidebar / related services).
     * Randomized so visitors discover something new on every page load.
     */
    public function relatedServices(int $limit = 6): Collection
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
}
