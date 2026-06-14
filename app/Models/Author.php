<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatedFields;
use Database\Factories\AuthorFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    /** @use HasFactory<AuthorFactory> */
    use HasFactory;

    use HasTranslatedFields;

    protected $guarded = [];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'bio' => 'array',
            'same_as' => 'array',
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

    /** Public profile URL: /authors/{slug} (locale-prefixed for non-default languages). */
    public function url(string $language = Post::DEFAULT_LANGUAGE): string
    {
        return $language === Post::DEFAULT_LANGUAGE
            ? route('authors.show', $this->slug)
            : route('authors.show.localized', [$language, $this->slug]);
    }

    public function photoUrl(): ?string
    {
        if (! $this->photo) {
            return null;
        }

        return rtrim((string) config('filesystems.disks.r2.url'), '/').'/'.ltrim($this->photo, '/');
    }

    /** Name with credentials appended, e.g. "Dr. Ayşe Yılmaz, MD". */
    public function nameWithCredentials(): string
    {
        return $this->credentials ? $this->name.', '.$this->credentials : $this->name;
    }

    /**
     * Profile/social URLs for the Person `sameAs`.
     *
     * @return list<string>
     */
    public function profileLinks(): array
    {
        return collect((array) $this->same_as)
            ->filter(static fn ($url): bool => is_string($url) && trim($url) !== '')
            ->values()
            ->all();
    }
}
