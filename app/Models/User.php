<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
        'email',
        'password',
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
        ];
    }
}
