<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatedFields;
use Database\Factories\PricingFeatureFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PricingFeature extends Model
{
    /** @use HasFactory<PricingFeatureFactory> */
    use HasFactory;

    use HasTranslatedFields;

    protected $guarded = [];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'label' => 'array',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** @return BelongsTo<CostPage, $this> */
    public function page(): BelongsTo
    {
        return $this->belongsTo(CostPage::class, 'cost_page_id');
    }

    /** @return HasMany<PricingFeatureValue, $this> */
    public function values(): HasMany
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

    public function valueFor(PricingTier|int $tier): ?PricingFeatureValue
    {
        $id = $tier instanceof PricingTier ? $tier->id : $tier;

        return $this->values->firstWhere('pricing_tier_id', $id);
    }

    /**
     * Whether every given tier has the same value (so the row can be hidden
     * by "show differences only").
     *
     * @param  iterable<PricingTier>  $tiers
     */
    public function isSameForAll(iterable $tiers, ?string $locale = null): bool
    {
        $seen = [];

        foreach ($tiers as $tier) {
            $value = $this->valueFor($tier);
            $seen[] = $value?->is_included ? ($value->translate('value', $locale) ?: '1') : '0';
        }

        return count(array_unique($seen)) <= 1;
    }
}
