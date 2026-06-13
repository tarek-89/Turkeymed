<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Rank Math-style on-page SEO analysis for the post form.
 * Pure functions over the current form state — no I/O.
 */
class SeoAnalyzer
{
    /**
     * @return array{
     *     serp: array{url: string, title: string, description: string},
     *     checks: list<array{label: string, status: 'pass'|'fail'|'warn'}>,
     * }
     */
    public static function analyze(
        ?string $title,
        ?string $metaTitle,
        ?string $metaDescription,
        ?string $slug,
        ?string $language,
        ?string $body,
        ?string $keyword,
    ): array {
        $effectiveTitle = filled($metaTitle) ? $metaTitle : trim(($title ?? '').' - '.config('app.name'), ' -');
        $plainBody = trim(preg_replace('/\s+/u', ' ', strip_tags($body ?? '')) ?? '');
        $wordCount = $plainBody === '' ? 0 : count(preg_split('/\s+/u', $plainBody) ?: []);
        $description = filled($metaDescription) ? $metaDescription : Str::limit($plainBody, 160);

        $path = ($language && $language !== 'en' ? $language.'/' : '').($slug ?? '');
        $url = rtrim(config('app.url'), '/').'/'.$path;

        $checks = [];

        $titleLength = mb_strlen($effectiveTitle);
        $checks[] = [
            'label' => "SEO title is {$titleLength} characters".($titleLength > 60 ? ' — will be cut off in Google (max ~60)' : ' (max ~60)'),
            'status' => $titleLength > 0 && $titleLength <= 60 ? 'pass' : ($titleLength === 0 ? 'fail' : 'warn'),
        ];

        $descriptionLength = mb_strlen($metaDescription ?? '');
        $checks[] = match (true) {
            $descriptionLength === 0 => ['label' => 'No meta description set — Google will pick its own snippet', 'status' => 'warn'],
            $descriptionLength < 120 => ['label' => "Meta description is {$descriptionLength} characters — could use more detail (aim 120-160)", 'status' => 'warn'],
            $descriptionLength <= 160 => ['label' => "Meta description is {$descriptionLength} characters — good length", 'status' => 'pass'],
            default => ['label' => "Meta description is {$descriptionLength} characters — will be cut off (max ~160)", 'status' => 'warn'],
        };

        $checks[] = [
            'label' => $wordCount >= 600
                ? "Content is {$wordCount} words long — good job!"
                : "Content is {$wordCount} words — aim for 600+",
            'status' => $wordCount >= 600 ? 'pass' : 'warn',
        ];

        if (blank($keyword)) {
            $checks[] = ['label' => 'Set a focus keyword to unlock keyword checks', 'status' => 'warn'];

            return [
                'serp' => ['url' => $url, 'title' => $effectiveTitle, 'description' => $description ?? ''],
                'checks' => $checks,
            ];
        }

        $keywordLower = mb_strtolower(trim($keyword));
        $contains = fn (?string $haystack): bool => $haystack !== null
            && mb_stripos($haystack, $keywordLower) !== false;

        $checks[] = [
            'label' => 'Focus keyword in the SEO title',
            'status' => $contains($effectiveTitle) ? 'pass' : 'fail',
        ];

        $checks[] = [
            'label' => 'Focus keyword in the meta description',
            'status' => $contains($metaDescription) ? 'pass' : 'fail',
        ];

        $slugMatches = $contains(str_replace('-', ' ', $slug ?? ''))
            || $contains($slug)
            || str_contains($slug ?? '', Str::slug($keywordLower));
        $checks[] = [
            'label' => 'Focus keyword in the URL',
            'status' => $slugMatches ? 'pass' : 'fail',
        ];

        $first10Percent = mb_substr($plainBody, 0, max(200, (int) (mb_strlen($plainBody) * 0.1)));
        $checks[] = [
            'label' => 'Focus keyword at the beginning of the content',
            'status' => $contains($first10Percent) ? 'pass' : 'fail',
        ];

        $occurrences = $plainBody === '' ? 0 : mb_substr_count(mb_strtolower($plainBody), $keywordLower);
        $checks[] = [
            'label' => $occurrences > 0
                ? "Focus keyword used {$occurrences}x in the content"
                : 'Focus keyword not found in the content',
            'status' => $occurrences > 0 ? 'pass' : 'fail',
        ];

        if ($occurrences > 0 && $wordCount > 0) {
            $density = round($occurrences / $wordCount * 100, 2);
            $checks[] = [
                'label' => "Keyword density is {$density}% (recommended 0.5% - 2.5%)",
                'status' => $density >= 0.5 && $density <= 2.5 ? 'pass' : 'warn',
            ];
        }

        return [
            'serp' => ['url' => $url, 'title' => $effectiveTitle, 'description' => $description ?? ''],
            'checks' => $checks,
        ];
    }
}
