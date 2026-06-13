<?php

namespace App\Console\Commands;

use App\Models\Service;
use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Imports WordPress pages (services) into the Laravel `services` table.
 *
 * Reads from the `wordpress` connection (legacy DB copy):
 *  - wp_posts (post_type = 'page')
 *  - Polylang: language + translation groups
 *  - Rank Math: meta title / description / focus keyword
 *  - Elementor: flags pages whose content lives in _elementor_data
 *  - Featured images: _thumbnail_id -> _wp_attached_file
 *
 * Category assignment is manual via Filament after import — WP pages
 * don't use categories. The seeded ServiceCategory records match the
 * top-level menu groups (Hair Transplant Surgery, Dental Clinic, etc.).
 *
 * Idempotent: upserts on wp_post_id, safe to re-run.
 *
 * Usage:
 *   php artisan wp:import-services --dry-run          # analyze only
 *   php artisan wp:import-services                    # full import
 *   php artisan wp:import-services --since=2026-06-12 # delta sync
 *   php artisan wp:import-services --exclude=homepage,contact  # skip by slug
 */
class ImportWordPressServices extends Command
{
    protected $signature = 'wp:import-services
        {--dry-run : Analyze and report without writing anything}
        {--since= : Only import pages modified on/after this date (delta sync)}
        {--limit= : Max number of pages to process}
        {--status=publish,draft : Comma-separated WP post statuses to import}
        {--exclude= : Comma-separated slugs to skip (e.g. homepage, contact, about-us)}';

    protected $description = 'Import WordPress pages (services) into the services table';

    /** @var array<int,string> wp post ID => language slug */
    private array $languageMap = [];

    /** @var array<int,int> wp post ID => translation group id */
    private array $groupMap = [];

    /** @var array<int,string> wp user ID => display name */
    private array $authorMap = [];

    public function handle(): int
    {
        $wp = DB::connection('wordpress');

        $this->loadLanguageMap($wp);
        $this->loadTranslationGroups($wp);
        $this->loadAuthors($wp);

        $this->info(sprintf(
            'Polylang: %d pages have a language, %d translation groups.',
            count($this->languageMap),
            count(array_unique($this->groupMap)),
        ));

        $statuses = array_map('trim', explode(',', (string) $this->option('status')));
        $excludeSlugs = $this->option('exclude')
            ? array_map('trim', explode(',', (string) $this->option('exclude')))
            : [];

        $query = $wp->table('posts')
            ->where('post_type', 'page')
            ->whereIn('post_status', $statuses)
            ->orderBy('ID');

        if ($excludeSlugs !== []) {
            $query->whereNotIn('post_name', $excludeSlugs);
            $this->info('Excluding slugs: '.implode(', ', $excludeSlugs));
        }

        if ($since = $this->option('since')) {
            $query->where('post_modified_gmt', '>=', $since);
        }
        if ($limit = $this->option('limit')) {
            $query->limit((int) $limit);
        }

        $total = (clone $query)->count();
        $this->info("Processing {$total} pages".($this->option('dry-run') ? ' (DRY RUN)' : '').'...');

        $stats = [
            'processed' => 0,
            'elementor' => 0,
            'missing_language' => 0,
            'missing_slug' => 0,
            'duplicate_slugs' => 0,
        ];
        $seenSlugs = [];
        $duplicates = [];
        $preview = [];
        $bar = $this->output->createProgressBar($total);

        $query->chunk(200, function ($pages) use ($wp, &$stats, &$seenSlugs, &$duplicates, &$preview, $bar) {
            $ids = $pages->pluck('ID')->all();
            $meta = $this->loadMeta($wp, $ids);
            $thumbnails = $this->loadThumbnailPaths($wp, $meta);

            $rows = [];
            foreach ($pages as $page) {
                $m = $meta[$page->ID] ?? [];

                $language = $this->languageMap[$page->ID] ?? null;
                if ($language === null) {
                    $stats['missing_language']++;
                    $language = 'en';
                }

                $isElementor = isset($m['_elementor_data']) && strlen((string) $m['_elementor_data']) > 10;
                if ($isElementor) {
                    $stats['elementor']++;
                }

                $slug = $page->post_name !== ''
                    ? rawurldecode($page->post_name)
                    : (Str::slug($page->post_title) ?: 'service-'.$page->ID);
                if ($page->post_name === '') {
                    $stats['missing_slug']++;
                }

                $slugKey = $language.'|'.$slug;
                if (isset($seenSlugs[$slugKey])) {
                    $stats['duplicate_slugs']++;
                    $duplicates[] = "[{$language}] {$slug} (wp_post_id {$page->ID})";
                }
                $seenSlugs[$slugKey] = true;

                $rows[] = [
                    'wp_post_id' => $page->ID,
                    'service_category_id' => null, // assigned manually via Filament after import
                    'translation_group_id' => $this->groupMap[$page->ID] ?? null,
                    'language' => $language,
                    'slug' => Str::limit($slug, 300, ''),
                    'title' => Str::limit($page->post_title ?: '(untitled)', 500, ''),
                    'excerpt' => $page->post_excerpt ?: null,
                    'body' => $this->cleanBody($page->post_content),
                    'featured_image' => $thumbnails[$page->ID] ?? null,
                    'author' => $this->authorMap[$page->post_author] ?? null,
                    'meta_title' => isset($m['rank_math_title']) ? Str::limit($m['rank_math_title'], 500, '') : null,
                    'meta_description' => isset($m['rank_math_description']) ? Str::limit($m['rank_math_description'], 1000, '') : null,
                    'focus_keyword' => isset($m['rank_math_focus_keyword']) ? Str::limit($m['rank_math_focus_keyword'], 255, '') : null,
                    'is_elementor' => $isElementor,
                    'status' => $page->post_status,
                    'published_at' => $this->safeDate($page->post_date_gmt),
                    'wp_modified_at' => $this->safeDate($page->post_modified_gmt),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $stats['processed']++;

                if (count($preview) < 30) {
                    $preview[] = [
                        $page->ID,
                        $this->groupMap[$page->ID] ?? '-',
                        $language,
                        Str::limit($slug, 50),
                        $isElementor ? 'YES' : 'no',
                        mb_strlen((string) $page->post_content).' chars',
                    ];
                }
            }

            if (! $this->option('dry-run') && $rows !== []) {
                Service::upsert(
                    $rows,
                    ['wp_post_id'],
                    [
                        'translation_group_id', 'language', 'slug', 'title', 'excerpt', 'body',
                        'featured_image', 'author', 'meta_title', 'meta_description', 'focus_keyword',
                        'is_elementor', 'status', 'published_at', 'wp_modified_at', 'updated_at',
                    ],
                );
            }

            $bar->advance(count($pages));
        });

        $bar->finish();
        $this->newLine(2);

        if ($preview !== []) {
            $this->line('Sample of processed pages:');
            $this->table(['WP ID', 'Group', 'Lang', 'Slug', 'Elementor', 'Body size'], $preview);
        }

        $this->table(['Metric', 'Count'], [
            ['Pages processed', $stats['processed']],
            ['Elementor-built (need render/rebuild decision)', $stats['elementor']],
            ['Missing Polylang language (defaulted to en)', $stats['missing_language']],
            ['Missing slug (generated from title)', $stats['missing_slug']],
            ['Duplicate language+slug pairs', $stats['duplicate_slugs']],
        ]);

        if ($duplicates !== []) {
            $this->warn('Duplicate slugs (same language) — review these:');
            foreach (array_slice($duplicates, 0, 50) as $dup) {
                $this->line('  '.$dup);
            }
            if (count($duplicates) > 50) {
                $this->line('  ... and '.(count($duplicates) - 50).' more');
            }
        }

        if ($this->option('dry-run')) {
            $this->info('Dry run complete — nothing was written.');
        } else {
            $this->info('Import complete. Total services in Laravel: '.Service::count());
        }

        return self::SUCCESS;
    }

    private function loadLanguageMap(ConnectionInterface $wp): void
    {
        $rows = $wp->table('term_relationships as tr')
            ->join('term_taxonomy as tt', 'tt.term_taxonomy_id', '=', 'tr.term_taxonomy_id')
            ->join('terms as t', 't.term_id', '=', 'tt.term_id')
            ->where('tt.taxonomy', 'language')
            ->select('tr.object_id', 't.slug')
            ->get();

        foreach ($rows as $row) {
            $this->languageMap[(int) $row->object_id] = $row->slug;
        }
    }

    private function loadTranslationGroups(ConnectionInterface $wp): void
    {
        $rows = $wp->table('term_taxonomy')
            ->where('taxonomy', 'post_translations')
            ->select('term_taxonomy_id', 'description')
            ->get();

        foreach ($rows as $row) {
            $map = @unserialize((string) $row->description, ['allowed_classes' => false]);
            if (! is_array($map)) {
                continue;
            }
            foreach ($map as $postId) {
                $this->groupMap[(int) $postId] = (int) $row->term_taxonomy_id;
            }
        }
    }

    private function loadAuthors(ConnectionInterface $wp): void
    {
        $this->authorMap = $wp->table('users')
            ->pluck('display_name', 'ID')
            ->map(fn ($name) => (string) $name)
            ->all();
    }

    /** @return array<int,array<string,string>> post_id => [meta_key => meta_value] */
    private function loadMeta(ConnectionInterface $wp, array $postIds): array
    {
        $keys = [
            '_thumbnail_id',
            '_elementor_data',
            'rank_math_title',
            'rank_math_description',
            'rank_math_focus_keyword',
        ];

        $meta = [];
        $rows = $wp->table('postmeta')
            ->whereIn('post_id', $postIds)
            ->whereIn('meta_key', $keys)
            ->select('post_id', 'meta_key', 'meta_value')
            ->get();

        foreach ($rows as $row) {
            $meta[(int) $row->post_id][$row->meta_key] = $row->meta_value;
        }

        return $meta;
    }

    /** @return array<int,string> post_id => relative path */
    private function loadThumbnailPaths(ConnectionInterface $wp, array $meta): array
    {
        $thumbIds = [];
        foreach ($meta as $postId => $m) {
            if (! empty($m['_thumbnail_id'])) {
                $thumbIds[$postId] = (int) $m['_thumbnail_id'];
            }
        }
        if ($thumbIds === []) {
            return [];
        }

        $files = $wp->table('postmeta')
            ->whereIn('post_id', array_unique(array_values($thumbIds)))
            ->where('meta_key', '_wp_attached_file')
            ->pluck('meta_value', 'post_id');

        $result = [];
        foreach ($thumbIds as $postId => $thumbId) {
            if (isset($files[$thumbId])) {
                $result[$postId] = $files[$thumbId];
            }
        }

        return $result;
    }

    private function cleanBody(?string $html): ?string
    {
        if (! $html) {
            return null;
        }

        $mediaBase = rtrim((string) config('filesystems.disks.r2.url'), '/');

        $html = str_replace(
            [
                'https://www.turkeymed.net/wp-content/uploads/',
                'https://turkeymed.net/wp-content/uploads/',
                'http://www.turkeymed.net/wp-content/uploads/',
                'http://turkeymed.net/wp-content/uploads/',
            ],
            $mediaBase.'/',
            $html,
        );

        $html = preg_replace('~(?<![\w.])/wp-content/uploads/~', $mediaBase.'/', $html);

        $html = str_replace(
            ['<h3>Send Us A Message</h3>', '<h2>More Information</h2>'],
            '',
            $html,
        );

        $html = preg_replace('/\t+/', ' ', $html);

        return trim($html) ?: null;
    }

    private function safeDate(?string $date): ?string
    {
        return ($date && ! str_starts_with($date, '0000')) ? $date : null;
    }
}
