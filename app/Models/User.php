<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Concerns\HasTranslatedFields;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasTranslatedFields, Notifiable;

    /**
     * Gate access to the Filament admin panel.
     *
     * If an allow-list is configured (config('site.admin_emails'), env
     * ADMIN_EMAILS, comma-separated) only those addresses may enter. When no
     * allow-list is set, any existing user may access — there is no public
     * registration, so the user set is already controlled.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        $allowed = array_filter(array_map(
            'trim',
            explode(',', (string) config('site.admin_emails', '')),
        ));

        if ($allowed === []) {
            return true;
        }

        return in_array(strtolower($this->email), array_map('strtolower', $allowed), true);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'email',
        'password',
        'credentials',
        'title',
        'photo',
        'bio',
        'same_as',
        'is_published',
        'sort_order',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'bio' => 'array',
            'same_as' => 'array',
            'is_published' => 'boolean',
        ];
    }

    /* ---------------- Author profile ---------------- */

    /**
     * Users that are published, public-facing author profiles.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
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
