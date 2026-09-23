<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatedFields;
use Database\Factories\RecoveryPhaseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecoveryPhase extends Model
{
    /** @use HasFactory<RecoveryPhaseFactory> */
    use HasFactory;

    use HasTranslatedFields;

    /** Band colours, mapped to design tokens in the chart script. */
    public const TONES = [
        'navy' => 'Navy (light)',
        'cyan-soft' => 'Cyan (light)',
        'cyan' => 'Cyan',
    ];

    protected $guarded = [];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'from_month' => 'float',
            'to_month' => 'float',
            'name' => 'array',
            'short_name' => 'array',
            'sort_order' => 'integer',
        ];
    }

    /** @return BelongsTo<CostPage, $this> */
    public function page(): BelongsTo
    {
        return $this->belongsTo(CostPage::class, 'cost_page_id');
    }
}
