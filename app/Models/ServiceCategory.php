<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceCategory extends Model
{
    /** @use HasFactory<\Database\Factories\ServiceCategoryFactory> */
    use HasFactory;

    protected $guarded = [];

    /** @return HasMany<Service, $this> */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    /** @return HasMany<Post, $this> */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * URL of the category listing for the given language.
     */
    public function url(string $language = Post::DEFAULT_LANGUAGE): string
    {
        return $language === Post::DEFAULT_LANGUAGE
            ? route('services.category', $this->slug)
            : route('services.category.localized', [$language, $this->slug]);
    }
}
