<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatedFields;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promise extends Model
{
    /** @use HasFactory<\Database\Factories\PromiseFactory> */
    use HasFactory;

    use HasTranslatedFields;

    /** Icons available to the admin; rendered by <x-ui.icon>. */
    public const ICONS = ['shield', 'clock', 'chat', 'star', 'heart', 'check'];

    protected $guarded = [];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'title' => 'array',
            'text' => 'array',
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
