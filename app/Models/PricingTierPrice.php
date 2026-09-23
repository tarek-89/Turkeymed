<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingTierPrice extends Model
{
    protected $guarded = [];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
        ];
    }

    /** @return BelongsTo<PricingTier, $this> */
    public function tier(): BelongsTo
    {
        return $this->belongsTo(PricingTier::class, 'pricing_tier_id');
    }

    /** @return BelongsTo<PricingTechnique, $this> */
    public function technique(): BelongsTo
    {
        return $this->belongsTo(PricingTechnique::class, 'pricing_technique_id');
    }
}
