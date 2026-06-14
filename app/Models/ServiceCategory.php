<?php

namespace App\Models;

use Database\Factories\ServiceCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceCategory extends Model
{
    /** @use HasFactory<ServiceCategoryFactory> */
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
     * URL of the service category listing (/services/{slug}). Kept as the
     * default url() because most callers (nav, footer) are service contexts.
     */
    public function url(string $language = Post::DEFAULT_LANGUAGE): string
    {
        return $this->serviceUrl($language);
    }

    /**
     * URL of the service category listing: /services/{slug}.
     */
    public function serviceUrl(string $language = Post::DEFAULT_LANGUAGE): string
    {
        return $language === Post::DEFAULT_LANGUAGE
            ? route('services.category', $this->slug)
            : route('services.category.localized', [$language, $this->slug]);
    }

    /**
     * URL of the blog category listing: /blog/{slug}.
     */
    public function blogUrl(string $language = Post::DEFAULT_LANGUAGE): string
    {
        return $language === Post::DEFAULT_LANGUAGE
            ? route('posts.category', $this->slug)
            : route('posts.category.localized', [$language, $this->slug]);
    }
}
