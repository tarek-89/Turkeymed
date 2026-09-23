<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatedFields;
use App\Support\Cost\CalculatorDefaults;
use App\Support\Cost\GraftCalculator;
use App\Support\Cost\SectionKey;
use App\Support\Locale;
use Database\Factories\CostPageFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Number;

/**
 * A pricing page ("Hair transplant cost in Turkey"). Translatable text is a
 * locale-keyed JSON column; the page is served at /pricing/{slug} and
 * /{locale}/pricing/{slug}.
 */
class CostPage extends Model
{
    /** @use HasFactory<CostPageFactory> */
    use HasFactory;

    use HasTranslatedFields;

    protected $guarded = [];

    /** Mirrors the column defaults so a freshly created model behaves like a reloaded one. */
    protected $attributes = [
        'is_published' => false,
        'is_unlisted' => false,
        'currency' => 'EUR',
        'hairs_per_graft' => 2.2,
        'session_cap_grafts' => 4500,
        'meter_max_grafts' => 5000,
    ];

    protected static function booted(): void
    {
        // Every page owns the full, fixed set of section rows so the admin can
        // toggle and reorder them right away.
        static::created(function (self $page): void {
            $page->ensureSections();
            $page->ensureZones();
        });
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'is_unlisted' => 'boolean',
            'title' => 'array',
            'meta_title' => 'array',
            'meta_description' => 'array',
            'price_range_min' => 'integer',
            'price_range_max' => 'integer',
            'hairs_per_graft' => 'float',
            'session_cap_grafts' => 'integer',
            'meter_max_grafts' => 'integer',
            'two_session_price_from' => 'integer',
            'duration_rules' => 'array',
            'default_zone_numbers' => 'array',
        ];
    }

    /* ---------------- Relationships ---------------- */

    /** @return BelongsTo<ServiceCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    /** @return HasMany<CostPageSection, $this> */
    public function sections(): HasMany
    {
        return $this->hasMany(CostPageSection::class)->orderBy('sort_order');
    }

    /** @return HasMany<GraftZone, $this> */
    public function zones(): HasMany
    {
        return $this->hasMany(GraftZone::class)->orderBy('sort_order')->orderBy('number');
    }

    /** @return HasMany<GraftPreset, $this> */
    public function presets(): HasMany
    {
        return $this->hasMany(GraftPreset::class)->orderBy('sort_order');
    }

    /** @return HasMany<CostCountry, $this> */
    public function countries(): HasMany
    {
        return $this->hasMany(CostCountry::class)->orderBy('sort_order');
    }

    /** @return HasMany<CostComparisonRow, $this> */
    public function comparisonRows(): HasMany
    {
        return $this->hasMany(CostComparisonRow::class)->orderBy('sort_order');
    }

    /** @return HasMany<CostProcedureStep, $this> */
    public function procedureSteps(): HasMany
    {
        return $this->hasMany(CostProcedureStep::class)->orderBy('sort_order');
    }

    /** @return HasMany<RecoveryStage, $this> */
    public function recoveryStages(): HasMany
    {
        return $this->hasMany(RecoveryStage::class)->orderBy('sort_order')->orderBy('month');
    }

    /** @return HasMany<RecoveryPhase, $this> */
    public function recoveryPhases(): HasMany
    {
        return $this->hasMany(RecoveryPhase::class)->orderBy('sort_order')->orderBy('from_month');
    }

    /** @return HasMany<PricingTechnique, $this> */
    public function techniques(): HasMany
    {
        return $this->hasMany(PricingTechnique::class)->orderBy('sort_order');
    }

    /** @return HasMany<PricingTier, $this> */
    public function tiers(): HasMany
    {
        return $this->hasMany(PricingTier::class)->orderBy('sort_order');
    }

    /** @return HasMany<PricingFeature, $this> */
    public function features(): HasMany
    {
        return $this->hasMany(PricingFeature::class)->orderBy('sort_order');
    }

    /* ---------------- Scopes ---------------- */

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * Published pages that may be linked and indexed (footer, sitemap, llms).
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeListed(Builder $query): Builder
    {
        return $query->published()->where('is_unlisted', false);
    }

    /** Whether a visitor's key opens this page (always true for pages without a key). */
    public function acceptsKey(?string $key): bool
    {
        if (! $this->is_unlisted || blank($this->access_key)) {
            return true;
        }

        return is_string($key) && hash_equals($this->access_key, $key);
    }

    /** The full share link of an unlisted page, key included. */
    public function shareUrl(?string $locale = null): string
    {
        $url = $this->url($locale);

        return $this->is_unlisted && filled($this->access_key)
            ? $url.'?key='.$this->access_key
            : $url;
    }

    /* ---------------- Sections ---------------- */

    /**
     * Create any section rows this page is missing (new section types added
     * after the page was created). Existing rows are left untouched.
     */
    public function ensureSections(): void
    {
        $existing = $this->sections()->pluck('key')->all();

        foreach (SectionKey::cases() as $position => $key) {
            if (! in_array($key->value, $existing, true)) {
                $this->sections()->create([
                    'key' => $key->value,
                    'sort_order' => $position + 1,
                ]);
            }
        }
    }

    /**
     * Create the six calculator zones (with the design's default names and
     * ranges) if the page has none yet. Zones are edited, never added.
     */
    public function ensureZones(): void
    {
        if ($this->zones()->exists()) {
            return;
        }

        foreach (CalculatorDefaults::zones() as $number => $zone) {
            $this->zones()->create([
                'number' => $number,
                'name' => [Locale::DEFAULT => $zone['name']],
                'min_grafts' => $zone['min'],
                'max_grafts' => $zone['max'],
                'sort_order' => $number,
            ]);
        }
    }

    public function calculator(): GraftCalculator
    {
        return new GraftCalculator($this);
    }

    public function section(SectionKey $key): ?CostPageSection
    {
        return $this->sections->firstWhere('key', $key->value);
    }

    /**
     * Sections to render, in admin order.
     *
     * @return Collection<int, CostPageSection>
     */
    public function visibleSections(): Collection
    {
        return $this->sections
            ->filter(fn (CostPageSection $section): bool => $section->is_visible && $section->sectionKey() !== null)
            ->values();
    }

    /* ---------------- Related content ---------------- */

    /**
     * Published, consented before/after results of the page's category.
     *
     * @return Collection<int, PatientResult>
     */
    public function patientResults(int $limit = 10): Collection
    {
        if (! $this->service_category_id) {
            return new Collection;
        }

        return PatientResult::published()
            ->where('service_category_id', $this->service_category_id)
            ->orderBy('sort_order')
            ->limit($limit)
            ->get();
    }

    /**
     * Latest published articles of the page's category, in the given language.
     *
     * @return Collection<int, Post>
     */
    public function relatedPosts(string $language, int $limit = 3): Collection
    {
        if (! $this->service_category_id) {
            return new Collection;
        }

        return Post::published()
            ->with('category')
            ->language($language)
            ->where('service_category_id', $this->service_category_id)
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    /* ---------------- URLs & SEO ---------------- */

    public function url(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $locale === Locale::DEFAULT
            ? route('cost.show', $this->slug)
            : route('cost.show.localized', [$locale, $this->slug]);
    }

    /**
     * hreflang alternates: only locales with a translated title.
     *
     * @return array<string, string>
     */
    public function alternates(): array
    {
        $alternates = [];

        foreach (Locale::codes() as $code) {
            if (filled(((array) $this->title)[$code] ?? null)) {
                $alternates[$code] = $this->url($code);
            }
        }

        return $alternates;
    }

    public function metaTitle(?string $locale = null): string
    {
        return $this->translate('meta_title', $locale)
            ?: str($this->translate('title', $locale).' - '.config('app.name'))->limit(60, '')->toString();
    }

    public function metaDescription(?string $locale = null): ?string
    {
        if ($description = $this->translate('meta_description', $locale)) {
            return $description;
        }

        $lead = $this->section(SectionKey::Calculator)?->translate('lead', $locale);

        return $lead ? str($lead)->limit(155)->toString() : null;
    }

    public function ogImageUrl(): ?string
    {
        if (! $this->og_image) {
            return null;
        }

        return rtrim((string) config('filesystems.disks.r2.url'), '/').'/'.ltrim($this->og_image, '/');
    }

    /* ---------------- Money ---------------- */

    /**
     * The "Turkey" price range shown on the page. Explicit values win; later
     * phases fall back to the cheapest / dearest package price.
     *
     * @return array{min: int|null, max: int|null}
     */
    public function priceRange(): array
    {
        $prices = $this->publishedPackagePrices();

        return [
            'min' => $this->price_range_min ?? ($prices === [] ? null : min($prices)),
            'max' => $this->price_range_max ?? ($prices === [] ? null : max($prices)),
        ];
    }

    /**
     * Every price of a published tier × published technique.
     *
     * @return list<int>
     */
    public function publishedPackagePrices(): array
    {
        $techniqueIds = $this->techniques->where('is_published', true)->pluck('id')->all();

        return $this->tiers
            ->where('is_published', true)
            ->flatMap(fn (PricingTier $tier) => $tier->prices
                ->whereIn('pricing_technique_id', $techniqueIds)
                ->pluck('price'))
            ->filter(fn ($price): bool => $price !== null)
            ->map(fn ($price): int => (int) $price)
            ->values()
            ->all();
    }

    /** Midpoint of the Turkey range, the basis of every comparison. */
    public function priceMidpoint(): ?float
    {
        $range = $this->priceRange();

        if ($range['min'] === null || $range['max'] === null) {
            return null;
        }

        return ($range['min'] + $range['max']) / 2;
    }

    /**
     * How much cheaper Turkey is than a country, from range midpoints.
     *
     * @return array{percent: int, amount: int, bar: float} bar = Turkey's bar width in % of the other country's
     */
    public function savingsFor(CostCountry $country): array
    {
        $turkey = $this->priceMidpoint();
        $other = $country->midpoint();

        if ($turkey === null || $other <= 0) {
            return ['percent' => 0, 'amount' => 0, 'bar' => 100.0];
        }

        return [
            'percent' => max(0, (int) round((1 - $turkey / $other) * 100)),
            'amount' => max(0, (int) (round(($other - $turkey) / 50) * 50)),
            'bar' => round(min(100, $turkey / $other * 100), 2),
        ];
    }

    /**
     * The "from" price quoted by the calculator: the cheapest published
     * package, or the low end of the page range when there are no packages.
     */
    public function priceFrom(): ?int
    {
        $prices = $this->publishedPackagePrices();

        return $prices === [] ? $this->price_range_min : min($prices);
    }

    /** Format an amount in the page currency for the current locale, e.g. "€1,500" / "1 500 €". */
    public function money(int|float|null $amount, ?string $locale = null): string
    {
        if ($amount === null) {
            return '';
        }

        $locale ??= app()->getLocale();

        // Keep Latin digits in Arabic, matching the rest of the site's copy.
        if ($locale === 'ar') {
            $locale = 'ar@numbers=latn';
        }

        return (string) Number::currency($amount, in: $this->currency, locale: $locale, precision: 0);
    }
}
