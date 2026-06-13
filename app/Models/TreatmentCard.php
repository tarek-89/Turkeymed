<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatedFields;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreatmentCard extends Model
{
    /** @use HasFactory<\Database\Factories\TreatmentCardFactory> */
    use HasFactory;

    use HasTranslatedFields;

    /** @var array<string, string> */
    public const VARIANTS = [
        'feature' => 'Feature (large gradient)',
        'default' => 'Default (white card)',
        'cta' => 'Call to action (soft card)',
    ];

    protected $guarded = [];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'title' => 'array',
            'description' => 'array',
            'badge' => 'array',
            'footnote' => 'array',
            'is_published' => 'boolean',
        ];
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}
