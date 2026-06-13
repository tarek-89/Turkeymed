<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientResult extends Model
{
    /** @use HasFactory<\Database\Factories\PatientResultFactory> */
    use HasFactory;

    protected $guarded = [];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'consent_confirmed' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    /* ---------------- Relationships ---------------- */

    /** @return BelongsTo<ServiceCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    /** @return BelongsTo<Service, $this> */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /* ---------------- Scopes ---------------- */

    /**
     * Publicly visible results. Consent is enforced here as well as in the
     * admin form, so an unconsented result can never leak onto the site.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where('consent_confirmed', true);
    }

    /* ---------------- Image URLs ---------------- */

    public function beforeImageUrl(): string
    {
        return self::imageUrl($this->before_image);
    }

    public function afterImageUrl(): string
    {
        return self::imageUrl($this->after_image);
    }

    private static function imageUrl(string $path): string
    {
        return rtrim((string) config('filesystems.disks.r2.url'), '/').'/'.ltrim($path, '/');
    }
}
