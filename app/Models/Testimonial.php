<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatedFields;
use Database\Factories\TestimonialFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    /** @use HasFactory<TestimonialFactory> */
    use HasFactory;

    use HasTranslatedFields;

    protected $guarded = [];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'quote' => 'array',
            'author_meta' => 'array',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'rating' => 'integer',
        ];
    }

    public function avatarUrl(): ?string
    {
        if (! $this->avatar) {
            return null;
        }

        return rtrim((string) config('filesystems.disks.r2.url'), '/').'/'.ltrim($this->avatar, '/');
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
