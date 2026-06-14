<?php

namespace App\Support;

use App\Models\Post;
use App\Models\Service;

class ContentSlug
{
    /**
     * Posts and services are served from the same root URL namespace
     * (/{slug}), so a slug can only belong to one of them per language —
     * otherwise the catch-all silently shadows one with the other.
     *
     * Returns true when the slug is already taken by the *other* content type
     * in the given language.
     *
     * @param  'post'|'service'  $editing  The type currently being edited.
     */
    public static function takenByOtherType(string $editing, string $language, string $slug): bool
    {
        $otherType = $editing === 'post' ? Service::class : Post::class;

        return $otherType::query()
            ->where('language', $language)
            ->where('slug', $slug)
            ->exists();
    }
}
