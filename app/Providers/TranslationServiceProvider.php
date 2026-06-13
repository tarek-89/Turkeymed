<?php

namespace App\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\ServiceProvider;

class TranslationServiceProvider extends ServiceProvider
{
    /**
     * Load the categorised JSON translation files.
     *
     * Each supported locale has a directory of JSON files grouped by section
     * (lang/en/nav.json, lang/en/footer.json, ...). The file name becomes the
     * group, so lang/en/nav.json's {"cta": "..."} is read as __('nav.cta').
     *
     * Files are flattened with dot notation, so nested JSON objects are fine:
     * {"links": {"home": "Home"}} in nav.json => __('nav.links.home').
     */
    public function boot(): void
    {
        foreach (array_keys(config('locales.supported', [])) as $locale) {
            $directory = lang_path($locale);

            if (! is_dir($directory)) {
                continue;
            }

            foreach (glob($directory.'/*.json') ?: [] as $file) {
                $group = pathinfo($file, PATHINFO_FILENAME);
                $lines = json_decode((string) file_get_contents($file), true);

                if (! is_array($lines)) {
                    continue;
                }

                Lang::addLines(Arr::dot([$group => $lines]), $locale);
            }
        }
    }
}
