<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatedFields;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    /** @use HasFactory<\Database\Factories\GalleryFactory> */
    use HasFactory;

    use HasTranslatedFields;

    /** @var array<string, string> */
    public const LAYOUTS = [
        'grid' => 'Grid',
        'slider' => 'Slider',
    ];

    protected $guarded = [];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'title' => 'array',
            'description' => 'array',
            'images' => 'array',
            'is_published' => 'boolean',
        ];
    }

    /**
     * Absolute URLs for the gallery's images.
     *
     * @return list<string>
     */
    public function imageUrls(): array
    {
        $base = rtrim((string) config('filesystems.disks.r2.url'), '/');

        return array_map(
            fn (string $path): string => $base.'/'.ltrim($path, '/'),
            array_values((array) $this->images),
        );
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
