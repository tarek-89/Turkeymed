<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatedFields;
use Database\Factories\CostCountryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CostCountry extends Model
{
    /** @use HasFactory<CostCountryFactory> */
    use HasFactory;

    use HasTranslatedFields;

    protected $guarded = [];

    protected static function booted(): void
    {
        // Only one default country per page.
        static::saved(function (self $country): void {
            if ($country->is_default) {
                static::query()
                    ->where('cost_page_id', $country->cost_page_id)
                    ->where('id', '!=', $country->id)
                    ->update(['is_default' => false]);
            }
        });
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'name' => 'array',
            'name_in_sentence' => 'array',
            'note' => 'array',
            'min_price' => 'integer',
            'max_price' => 'integer',
            'is_default' => 'boolean',
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

    public function midpoint(): float
    {
        return ($this->min_price + $this->max_price) / 2;
    }

    /** The name as used mid-sentence ("the United Kingdom"), falling back to the plain name. */
    public function sentenceName(?string $locale = null): ?string
    {
        return $this->translate('name_in_sentence', $locale) ?: $this->translate('name', $locale);
    }
}
