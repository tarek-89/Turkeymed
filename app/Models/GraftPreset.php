<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatedFields;
use Database\Factories\GraftPresetFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GraftPreset extends Model
{
    /** @use HasFactory<GraftPresetFactory> */
    use HasFactory;

    use HasTranslatedFields;

    protected $guarded = [];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'label' => 'array',
            'zone_numbers' => 'array',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** @return BelongsTo<CostPage, $this> */
    public function page(): BelongsTo
    {
        return $this->belongsTo(CostPage::class, 'cost_page_id');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /** @return list<int> */
    public function zoneNumbers(): array
    {
        return array_values(array_map('intval', (array) $this->zone_numbers));
    }
}
