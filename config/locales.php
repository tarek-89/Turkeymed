<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Supported locales
    |--------------------------------------------------------------------------
    |
    | Every language the public site is available in. The key is the 2-letter
    | locale code used in URLs (e.g. /fr/...). English ('en') is the default
    | and is served at the root with no prefix.
    |
    |   native   — the language's own name, shown in the switcher.
    |   dir      — text direction: 'ltr' or 'rtl' (Arabic is 'rtl').
    |
    | To add a language: add an entry here, then create the matching
    | translation files in lang/{code}/ (copy the lang/en/ files and translate).
    |
    */

    'supported' => [
        'en' => ['native' => 'English',  'dir' => 'ltr'],
        'fr' => ['native' => 'Français', 'dir' => 'ltr'],
        'es' => ['native' => 'Español',  'dir' => 'ltr'],
        'ar' => ['native' => 'العربية',  'dir' => 'rtl'],
    ],

];
