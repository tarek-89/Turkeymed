<?php

namespace App\Models;

use Database\Factories\RedirectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Redirect extends Model
{
    /** @use HasFactory<RedirectFactory> */
    use HasFactory;

    protected $guarded = [];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'status_code' => 'integer',
            'hits' => 'integer',
            'last_hit_at' => 'datetime',
        ];
    }

    /**
     * Normalise a request path to the form stored in `from_path`:
     * percent-decoded, no surrounding slashes (e.g. "/category/Hair/" -> "category/Hair").
     */
    public static function normalizePath(string $path): string
    {
        return trim(rawurldecode($path), '/');
    }

    /**
     * The active redirect for a given request path, or null when there is none.
     */
    public static function match(string $path): ?self
    {
        $key = self::normalizePath($path);

        if ($key === '') {
            return null;
        }

        return static::query()
            ->where('is_active', true)
            ->where('from_path', $key)
            ->first();
    }

    /**
     * Destination as something the redirect helper can use: absolute URLs are
     * passed through, everything else is treated as a root-relative path.
     */
    public function target(): string
    {
        if (Str::startsWith($this->to_path, ['http://', 'https://', '/'])) {
            return $this->to_path;
        }

        return '/'.$this->to_path;
    }

    /**
     * Record that the redirect was followed, without touching `updated_at`.
     */
    public function recordHit(): void
    {
        $this->forceFill([
            'hits' => $this->hits + 1,
            'last_hit_at' => now(),
        ])->saveQuietly();
    }
}
