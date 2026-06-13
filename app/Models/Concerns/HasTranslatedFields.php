<?php

namespace App\Models\Concerns;

use App\Support\Locale;

/**
 * For models storing translatable fields as JSON keyed by locale code,
 * e.g. title = {"en": "Safety first", "fr": "La sécurité d'abord"}.
 */
trait HasTranslatedFields
{
    /**
     * The field's value in the current locale, falling back to the default
     * locale, then to any available translation.
     */
    public function translate(string $field, ?string $locale = null): ?string
    {
        $values = (array) $this->getAttribute($field);
        $locale ??= app()->getLocale();

        return $values[$locale]
            ?? $values[Locale::DEFAULT]
            ?? (array_values(array_filter($values))[0] ?? null);
    }
}
