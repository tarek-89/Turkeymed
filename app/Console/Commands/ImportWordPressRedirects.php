<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\Redirect;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Support\Locale;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportWordPressRedirects extends Command
{
    protected $signature = 'redirects:wordpress
        {--index=https://www.turkeymed.net/sitemap_index.xml : URL of the old WordPress sitemap index}
        {--apply : Persist redirect rows (otherwise dry-run)}
        {--source=wp-import : Value stored in the redirects.source column}';

    protected $description = 'Audit the live WordPress sitemap and seed redirects for URLs whose slug no longer resolves.';

    public function handle(): int
    {
        $index = (string) $this->option('index');
        $apply = (bool) $this->option('apply');

        $this->info("Reading sitemap index: {$index}");

        $sitemaps = $this->locs($this->fetch($index));

        if ($sitemaps === []) {
            $this->error('No sub-sitemaps found. Is the index URL correct and reachable?');

            return self::FAILURE;
        }

        $oldUrls = [];
        foreach ($sitemaps as $sitemap) {
            $this->line("  • {$sitemap}");
            $oldUrls = array_merge($oldUrls, $this->locs($this->fetch($sitemap)));
        }

        $oldUrls = array_values(array_unique($oldUrls));
        $this->info(count($oldUrls).' URLs found in the old sitemap.');

        $preserved = 0;
        $review = [];

        foreach ($oldUrls as $url) {
            $path = Redirect::normalizePath((string) parse_url($url, PHP_URL_PATH));

            if ($path === '') {
                continue; // homepage
            }

            [$resolves, $suggestion] = $this->resolve($path);

            if ($resolves) {
                $preserved++;

                continue; // slug preserved — the trailing-slash middleware handles "/path/" -> "/path"
            }

            $review[] = ['from' => $path, 'to' => $suggestion];

            if ($apply) {
                Redirect::query()->updateOrCreate(
                    ['from_path' => $path],
                    [
                        'to_path' => $suggestion ?? '/',
                        'status_code' => 301,
                        'is_active' => false,
                        'source' => (string) $this->option('source'),
                        'notes' => $suggestion !== null
                            ? 'Auto-suggested from the leaf slug — confirm the destination, then activate.'
                            : 'No match found — set the destination, then activate.',
                    ],
                );
            }
        }

        $this->newLine();
        $this->info("Flat slugs handled by redirects:restructure: {$preserved}");
        $this->warn('Old hierarchical/renamed URLs needing review: '.count($review).' (saved inactive)');

        foreach (array_slice($review, 0, 40) as $entry) {
            $target = $entry['to'] ?? '(no suggestion — set manually)';
            $this->line("  /{$entry['from']}  →  {$target}");
        }

        if (count($review) > 40) {
            $this->line('  … '.(count($review) - 40).' more');
        }

        $this->newLine();

        if ($apply) {
            $this->info('Saved inactive redirect rows. Confirm each destination and activate it, then they go live.');
        } else {
            $this->comment('Dry run. Re-run with --apply to save these as inactive redirects for review.');
        }

        return self::SUCCESS;
    }

    /**
     * Resolve an old path against the new structure.
     *
     * @return array{0: bool, 1: ?string} [stillResolves, suggestedDestination]
     *                                    - stillResolves true  => only the trailing slash changed, no redirect needed.
     *                                    - stillResolves false => a redirect is needed; the second element is a
     *                                    best-guess destination (or null when nothing matched).
     */
    private function resolve(string $path): array
    {
        $segments = explode('/', $path);

        $language = Post::DEFAULT_LANGUAGE;
        if (Locale::isSupported($segments[0])) {
            $language = array_shift($segments);
        }

        if ($segments === []) {
            return [true, null]; // localized homepage
        }

        // Category listing: /category/{slug}
        if (count($segments) === 2 && $segments[0] === 'category') {
            if (ServiceCategory::query()->where('slug', $segments[1])->exists()) {
                return [true, null];
            }

            // Renamed category, e.g. "hair-transplant" -> "hair-transplant-surgery".
            $match = ServiceCategory::query()
                ->where('slug', 'like', $segments[1].'%')
                ->orWhere('slug', 'like', '%'.$segments[1].'%')
                ->first();

            return [false, $match?->url($language)];
        }

        // Single-segment post or service slug.
        if (count($segments) === 1) {
            if ($this->contentExists($language, $segments[0])) {
                return [true, null];
            }

            return [false, $this->suggestForSlug($language, $segments[0])];
        }

        // Deeper hierarchical WP URL (parent/child) that was flattened — match
        // the new page on the leaf slug.
        return [false, $this->suggestForSlug($language, end($segments))];
    }

    private function contentExists(string $language, string $slug): bool
    {
        return Post::query()->where('language', $language)->where('slug', $slug)->exists()
            || Service::query()->where('language', $language)->where('slug', $slug)->exists();
    }

    /**
     * Best-guess destination for a slug: the matching service or post URL,
     * preferring the same language, or null when nothing matches.
     */
    private function suggestForSlug(string $language, string $slug): ?string
    {
        foreach ([Service::class, Post::class] as $model) {
            $match = $model::query()
                ->where('slug', $slug)
                ->orderByRaw('language = ? desc', [$language])
                ->first();

            if ($match !== null) {
                return $match->url();
            }
        }

        return null;
    }

    private function fetch(string $url): string
    {
        $response = Http::timeout(20)->retry(2, 500)->get($url);

        if ($response->failed()) {
            $this->warn("  ! Failed to fetch {$url} ({$response->status()})");

            return '';
        }

        return $response->body();
    }

    /**
     * Extract <loc> values from sitemap XML without caring about namespaces.
     *
     * @return list<string>
     */
    private function locs(string $xml): array
    {
        if ($xml === '' || preg_match_all('/<loc>\s*([^<]+?)\s*<\/loc>/i', $xml, $matches) === false) {
            return [];
        }

        return array_map('trim', $matches[1] ?? []);
    }
}
