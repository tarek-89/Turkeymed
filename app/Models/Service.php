<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    /** @use HasFactory<\Database\Factories\ServiceFactory> */
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

    /** Reuse the same language constants from Post. */
    public function url(): string
    {
        return $this->language === Post::DEFAULT_LANGUAGE
            ? url('/'.$this->slug)
            : url('/'.$this->language.'/'.$this->slug);
    }

    public function featuredImageUrl(): ?string
    {
        if (! $this->featured_image) {
            return null;
        }

        return rtrim((string) config('filesystems.disks.r2.url'), '/').'/'.ltrim($this->featured_image, '/');
    }

    public function metaTitle(): string
    {
        return $this->meta_title ?: $this->title.' - '.config('app.name');
    }

    public function metaDescription(): ?string
    {
        return $this->meta_description ?: ($this->excerpt ? str($this->excerpt)->limit(155)->toString() : null);
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
}
