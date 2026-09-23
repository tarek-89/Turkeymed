<?php

namespace App\Filament\Support;

use App\Support\Locale;
use Closure;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

/**
 * One tab per supported locale, each holding the same fields for that locale.
 * The callback receives the locale code and whether it is the default (which
 * is used to mark fields required in English only).
 *
 * Usage: TranslatedTabs::make('Translations', fn (string $code, bool $isDefault): array => [
 *     TextInput::make("title.{$code}")->required($isDefault),
 * ])
 */
class TranslatedTabs
{
    /**
     * @param  Closure(string $code, bool $isDefault): array<int, Component>  $fields
     */
    public static function make(string $label, Closure $fields): Tabs
    {
        return Tabs::make($label)
            ->tabs(
                collect(Locale::codes())
                    ->map(fn (string $code): Tab => Tab::make(strtoupper($code))
                        ->label(strtoupper($code).' · '.Locale::native($code))
                        ->schema($fields($code, $code === Locale::DEFAULT)))
                    ->all(),
            );
    }
}
