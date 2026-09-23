<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatedFields;
use App\Support\Locale;
use Database\Factories\PricingTierFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PricingTier extends Model
{
    /** @use HasFactory<PricingTierFactory> */
    use HasFactory;

    use HasTranslatedFields;

    protected $guarded = [];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'name' => 'array',
            'subtitle' => 'array',
            'highlights' => 'array',
            'cta_label' => 'array',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** @return BelongsTo<CostPage, $this> */
    public function page(): BelongsTo
    {
        return $this->belongsTo(CostPage::class, 'cost_page_id');
    }

    /** @return HasMany<PricingTierPrice, $this> */
    public function prices(): HasMany
    {
        return $this->hasMany(PricingTierPrice::class);
    }

    /** @return HasMany<PricingFeatureValue, $this> */
    public function featureValues(): HasMany
    {
        return $this->hasMany(PricingFeatureValue::class);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function priceFor(PricingTechnique|int $technique): ?int
    {
        $id = $technique instanceof PricingTechnique ? $technique->id : $technique;

        return $this->prices->firstWhere('pricing_technique_id', $id)?->price;
    }

    /**
     * Short tags shown under the price, in the current locale.
     *
     * @return list<string>
     */
    public function highlightList(?string $locale = null): array
    {
        $all = (array) $this->highlights;
        $locale ??= app()->getLocale();
        $list = $all[$locale] ?? $all[Locale::DEFAULT] ?? (array_values(array_filter($all))[0] ?? []);

        return array_values(array_filter(array_map('strval', (array) $list)));
    }
}
