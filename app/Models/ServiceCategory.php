<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatedFields;
use App\Support\Locale;
use Database\Factories\ServiceCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceCategory extends Model
{
    /** @use HasFactory<ServiceCategoryFactory> */
    use HasFactory;

    use HasTranslatedFields;

    protected $guarded = [];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'name' => 'array',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Move this category one position up (-1) or down (+1) in the header menu,
     * then renumber all categories 1..n so positions stay unique.
     */
    public function moveBy(int $direction): void
    {
        $categories = static::query()
            ->orderBy('sort_order')
            ->orderByRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"".Locale::DEFAULT."\"'))")
            ->get()
            ->values();

        $index = $categories->search(fn (self $category): bool => $category->is($this));
        $target = $index + $direction;

        if ($index === false || $target < 0 || $target >= $categories->count()) {
            return;
        }

        $reordered = $categories->all();
        [$reordered[$index], $reordered[$target]] = [$reordered[$target], $reordered[$index]];

        foreach ($reordered as $position => $category) {
            $category->update(['sort_order' => $position + 1]);
        }
    }

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
