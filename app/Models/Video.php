<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatedFields;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    /** @use HasFactory<\Database\Factories\VideoFactory> */
    use HasFactory;

    use HasTranslatedFields;

    /** @var array<string, string> */
    public const KINDS = [
        'video' => 'Video (16:9)',
        'short' => 'Short (9:16)',
    ];

    protected $guarded = [];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'title' => 'array',
            'is_published' => 'boolean',
        ];
    }

    /**
     * Parse the YouTube video/short ID from common URL shapes:
     * youtu.be/ID, watch?v=ID, /shorts/ID, /embed/ID.
     */
    public function youtubeId(): ?string
    {
        $url = (string) $this->youtube_url;

        if (preg_match('~(?:youtu\.be/|/shorts/|/embed/|[?&]v=)([A-Za-z0-9_-]{11})~', $url, $m)) {
            return $m[1];
        }

        // Bare ID
        if (preg_match('~^[A-Za-z0-9_-]{11}$~', trim($url))) {
            return trim($url);
        }

        return null;
    }

    public function embedUrl(): ?string
    {
        $id = $this->youtubeId();

        return $id ? 'https://www.youtube-nocookie.com/embed/'.$id : null;
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
