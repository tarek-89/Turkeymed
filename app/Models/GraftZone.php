<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatedFields;
use Database\Factories\GraftZoneFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GraftZone extends Model
{
    /** @use HasFactory<GraftZoneFactory> */
    use HasFactory;

    use HasTranslatedFields;

    /** Zones drawn on the head illustration; the calculator supports exactly these. */
    public const NUMBERS = [1, 2, 3, 4, 5, 6];

    protected $guarded = [];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'number' => 'integer',
            'name' => 'array',
            'min_grafts' => 'integer',
            'max_grafts' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /** @return BelongsTo<CostPage, $this> */
    public function page(): BelongsTo
    {
        return $this->belongsTo(CostPage::class, 'cost_page_id');
    }
}
