<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatedFields;
use Database\Factories\RecoveryStageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecoveryStage extends Model
{
    /** @use HasFactory<RecoveryStageFactory> */
    use HasFactory;

    use HasTranslatedFields;

    protected $guarded = [];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'month' => 'float',
            'percent' => 'integer',
            'when_label' => 'array',
            'short_label' => 'array',
            'title' => 'array',
            'body' => 'array',
            'tip' => 'array',
            'sort_order' => 'integer',
        ];
    }

    /** @return BelongsTo<CostPage, $this> */
    public function page(): BelongsTo
    {
        return $this->belongsTo(CostPage::class, 'cost_page_id');
    }
}
