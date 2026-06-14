<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\Redirect;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Support\Locale;
use Illuminate\Console\Command;

class GenerateStructureRedirects extends Command
{
    protected $signature = 'redirects:restructure {--apply : Persist the redirects (otherwise dry-run)}';

    protected $description = 'Create 301 redirects from the old flat URLs to the new /blog and /services structure.';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $created = 0;
        $skipped = 0;

        // Single-segment paths that must never be hijacked by a redirect, even
        // if some content happens to share the slug.
        $reserved = array_merge(
            ['blog', 'services', 'authors', 'about', 'contact', 'sitemap.xml'],
            Locale::codes(),
        );

        $record = function (string $oldPath, string $newPath) use ($apply, $reserved, &$created, &$skipped): void {
            $from = Redirect::normalizePath($oldPath);
            $to = '/'.ltrim($newPath, '/');

            if ($from === '' || $from === Redirect::normalizePath($to) || in_array($from, $reserved, true)) {
                $skipped++;

                return;
            }

            $created++;

            if ($apply) {
                Redirect::query()->updateOrCreate(
                    ['from_path' => $from],
                    [
                        'to_path' => $to,
                        'status_code' => 301,
                        'is_active' => true,
                        'source' => 'restructure',
                        'notes' => null,
                    ],
                );
            }
        };

        Post::published()->with('category')->cursor()->each(function (Post $post) use ($record): void {
            $record($this->oldFlatPath($post->language, $post->slug), (string) parse_url($post->url(), PHP_URL_PATH));
        });

        Service::published()->with('category')->cursor()->each(function (Service $service) use ($record): void {
            $record($this->oldFlatPath($service->language, $service->slug), (string) parse_url($service->url(), PHP_URL_PATH));
        });

        // Old WordPress category listings: /category/{slug} -> /services/{slug}.
        ServiceCategory::query()->cursor()->each(function (ServiceCategory $category) use ($record): void {
            $record('category/'.$category->slug, (string) parse_url($category->serviceUrl(), PHP_URL_PATH));
        });

        $this->newLine();
        $this->info(($apply ? 'Saved ' : 'Would create ').$created.' redirects.');

        if ($skipped > 0) {
            $this->line($skipped.' skipped (unchanged).');
        }

        if (! $apply) {
            $this->comment('Dry run. Re-run with --apply to persist them as active 301s.');
        }

        return self::SUCCESS;
    }

    private function oldFlatPath(string $language, string $slug): string
    {
        return $language === Post::DEFAULT_LANGUAGE
            ? $slug
            : $language.'/'.$slug;
    }
}
