<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatedFields;
use Database\Factories\OfficeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    /** @use HasFactory<OfficeFactory> */
    use HasFactory;

    use HasTranslatedFields;

    protected $guarded = [];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'country' => 'array',
            'name' => 'array',
            'address' => 'array',
            'hours' => 'array',
            'badge' => 'array',
            'is_published' => 'boolean',
            'is_primary' => 'boolean',
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
