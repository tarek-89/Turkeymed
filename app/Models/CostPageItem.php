<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatedFields;
use Database\Factories\CostPageItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CostPageItem extends Model
{
    /** @use HasFactory<CostPageItemFactory> */
    use HasFactory;

    use HasTranslatedFields;

    protected $guarded = [];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'title' => 'array',
            'body' => 'array',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** @return BelongsTo<CostPageSection, $this> */
    public function section(): BelongsTo
    {
        return $this->belongsTo(CostPageSection::class, 'cost_page_section_id');
    }
}
