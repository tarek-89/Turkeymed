<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialLink extends Model
{
    /** @use HasFactory<\Database\Factories\SocialLinkFactory> */
    use HasFactory;

    /**
     * Supported platforms => display name. The key also selects the brand
     * icon rendered by <x-ui.social-icon>.
     *
     * @var array<string, string>
     */
    public const PLATFORMS = [
        'instagram' => 'Instagram',
        'facebook' => 'Facebook',
        'youtube' => 'YouTube',
        'tiktok' => 'TikTok',
        'telegram' => 'Telegram',
        'x' => 'X (Twitter)',
        'linkedin' => 'LinkedIn',
        'whatsapp' => 'WhatsApp',
        'website' => 'Website / custom',
    ];

    protected $guarded = [];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
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

    /** Accessible label: the override, or the platform's display name. */
    public function displayLabel(): string
    {
        return $this->label ?: (self::PLATFORMS[$this->platform] ?? ucfirst($this->platform));
    }
}
