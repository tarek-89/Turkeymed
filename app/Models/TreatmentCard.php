<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatedFields;
use App\Support\Locale;
use App\Support\Navigation;
use App\Support\Url;
use Database\Factories\TreatmentCardFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreatmentCard extends Model
{
    /** @use HasFactory<TreatmentCardFactory> */
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
     * The card's link for the current (or given) locale.
     *
     * The stored `url` is a single value shared by every language. An internal
     * relative path (e.g. "/services/dental-clinic") is automatically prefixed
     * with the locale for non-default languages ("/ar/services/dental-clinic"),
     * matching the site's routing. External URLs and anchors are left as-is,
     * and an empty url falls back to the localised contact page.
     */
    public function href(?string $locale = null): string
    {
        $url = trim((string) $this->url);
        $locale ??= app()->getLocale();

        if ($url === '') {
            return Navigation::contactUrl();
        }

        // Only internal, root-relative paths get a locale prefix. Anchors,
        // query-only links and absolute/scheme URLs are returned untouched
        // (still neutralised against dangerous schemes by Url::safe()).
        if (Locale::isSupported($locale) && $locale !== Locale::DEFAULT && str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            $segments = array_values(array_filter(explode('/', $url)));

            // Don't double-prefix a path that already begins with a locale.
            if (! (isset($segments[0]) && Locale::isSupported($segments[0]))) {
                $url = '/'.$locale.$url;
            }
        }

        return Url::safe($url);
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
