<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $guarded = [];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    /** Read a setting (cached until it is next written). */
    public static function get(string $key, mixed $default = null): mixed
    {
        $value = cache()->rememberForever(
            'setting.'.$key,
            fn (): mixed => static::query()->where('key', $key)->value('value'),
        );

        return $value ?? $default;
    }

    /** Write a setting and bust its cache. */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);

        cache()->forget('setting.'.$key);
    }

    /**
     * A translated value from a locale-keyed setting,
     * e.g. get "about.heading" in the current locale.
     */
    public static function translated(string $key, ?string $locale = null): ?string
    {
        $values = (array) static::get($key, []);
        $locale ??= app()->getLocale();

        return $values[$locale]
            ?? $values[\App\Support\Locale::DEFAULT]
            ?? (array_values(array_filter($values))[0] ?? null);
    }
}
