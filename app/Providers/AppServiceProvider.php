<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        static::overrideSiteContactFromSettings();
    }

    /**
     * Let admin-editable settings override the config('site.*') contact
     * details. When a value is set on the Contact admin page it wins
     * everywhere config('site.*') is read; otherwise the .env / config
     * default is kept. Values are cached in the Setting model, so this
     * costs no per-request database query.
     *
     * Public + static so it can be re-applied within a single process
     * (e.g. in tests) after a setting has changed.
     */
    public static function overrideSiteContactFromSettings(): void
    {
        try {
            if (! Schema::hasTable('settings')) {
                return;
            }
        } catch (Throwable) {
            // Database not reachable yet (e.g. during install/migrate).
            return;
        }

        foreach (['phone', 'whatsapp', 'email'] as $key) {
            $value = Setting::get('site.'.$key);

            if (filled($value)) {
                config(['site.'.$key => $value]);
            }
        }
    }
}
