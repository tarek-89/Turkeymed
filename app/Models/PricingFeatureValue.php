<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatedFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingFeatureValue extends Model
{
    use HasTranslatedFields;

    protected $guarded = [];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_included' => 'boolean',
            'value' => 'array',
        ];
    }

    /** @return BelongsTo<PricingFeature, $this> */
    public function feature(): BelongsTo
    {
        return $this->belongsTo(PricingFeature::class, 'pricing_feature_id');
    }

    /** @return BelongsTo<PricingTier, $this> */
    public function tier(): BelongsTo
    {
        return $this->belongsTo(PricingTier::class, 'pricing_tier_id');
    }
}
