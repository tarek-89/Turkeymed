<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Imports WordPress posts into the Laravel `posts` table.
 *
 * Reads from the `wordpress` connection (legacy DB copy):
 *  - wp_posts (post_type = 'post')
 *  - Polylang: language + translation groups (taxonomies 'language' / 'post_translations')
 *  - Rank Math: meta title / description / focus keyword (wp_postmeta)
 *  - Elementor: flags posts whose content lives in _elementor_data
 *  - Featured images: _thumbnail_id -> _wp_attached_file (relative uploads/ path)
 *
 * Idempotent: upserts on wp_post_id, safe to re-run.
 *
 * Usage:
 *   php artisan wp:import-posts --multilang-groups=4 --dry-run   # test: analyze 4 multi-language groups
 *   php artisan wp:import-posts --multilang-groups=4             # test: import them
 *   php artisan wp:import-posts                                  # full import
 *   php artisan wp:import-posts --since=2026-06-12               # delta sync at cutover
 */
class ImportWordPressPosts extends Command
{
    protected $signature = 'wp:import-posts
        {--dry-run : Analyze and report without writing anything}
        {--multilang-groups= : TEST MODE - import only the first N translation groups having 2+ languages}
        {--since= : Only import posts modified on/after this date (delta sync)}
        {--limit= : Max number of posts to process}
        {--status=publish,draft : Comma-separated WP post statuses to import}';

    protected $description = 'Import WordPress posts (Polylang + Rank Math + Elementor flag) into the posts table';

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
            'Polylang: %d posts have a language, %d translation groups.',
            count($this->languageMap),
            count(array_unique($this->groupMap)),
        ));

        $statuses = array_map('trim', explode(',', (string) $this->option('status')));

        $query = $wp->table('posts')
            ->where('post_type', 'post')
            ->whereIn('post_status', $statuses)
            ->orderBy('ID');

        if ($groupCount = $this->option('multilang-groups')) {
            $postIds = $this->multilangGroupPostIds($wp, (int) $groupCount, $statuses);
            if ($postIds === []) {
                $this->error('No multi-language translation groups found among posts.');

                return self::FAILURE;
            }
            $this->info('TEST MODE: limiting to '.count($postIds).' posts from '.$groupCount.' multi-language groups.');
            $query->whereIn('ID', $postIds);
        }

        if ($since = $this->option('since')) {
            $query->where('post_modified_gmt', '>=', $since);
        }
        if ($limit = $this->option('limit')) {
            $query->limit((int) $limit);
        }

        $total = (clone $query)->count();
        $this->info("Processing {$total} posts".($this->option('dry-run') ? ' (DRY RUN)' : '').'...');

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

        $query->chunk(200, function ($posts) use ($wp, &$stats, &$seenSlugs, &$duplicates, &$preview, $bar) {
            $ids = $posts->pluck('ID')->all();
            $meta = $this->loadMeta($wp, $ids);
            $thumbnails = $this->loadThumbnailPaths($wp, $meta);

            $rows = [];
            foreach ($posts as $post) {
                $m = $meta[$post->ID] ?? [];

                $language = $this->languageMap[$post->ID] ?? null;
                if ($language === null) {
                    $stats['missing_language']++;
                    $language = 'en'; // fall back to default; review these in the report
                }

                $isElementor = isset($m['_elementor_data']) && strlen((string) $m['_elementor_data']) > 10;
                if ($isElementor) {
                    $stats['elementor']++;
                }

                // WP stores non-ASCII slugs percent-encoded; store readable UTF-8.
                $slug = $post->post_name !== ''
                    ? rawurldecode($post->post_name)
                    : (Str::slug($post->post_title) ?: 'post-'.$post->ID);
                if ($post->post_name === '') {
                    $stats['missing_slug']++;
                }

                $slugKey = $language.'|'.$slug;
                if (isset($seenSlugs[$slugKey])) {
                    $stats['duplicate_slugs']++;
                    $duplicates[] = "[{$language}] {$slug} (wp_post_id {$post->ID})";
                }
                $seenSlugs[$slugKey] = true;

                $rows[] = [
                    'wp_post_id' => $post->ID,
                    'translation_group_id' => $this->groupMap[$post->ID] ?? null,
                    'language' => $language,
                    'slug' => Str::limit($slug, 300, ''),
                    'title' => Str::limit($post->post_title ?: '(untitled)', 500, ''),
                    'excerpt' => $post->post_excerpt ?: null,
                    'body' => $this->cleanBody($post->post_content),
                    'featured_image' => $thumbnails[$post->ID] ?? null,
                    'author' => $this->authorMap[$post->post_author] ?? null,
                    'meta_title' => isset($m['rank_math_title']) ? Str::limit($m['rank_math_title'], 500, '') : null,
                    'meta_description' => isset($m['rank_math_description']) ? Str::limit($m['rank_math_description'], 1000, '') : null,
                    'focus_keyword' => isset($m['rank_math_focus_keyword']) ? Str::limit($m['rank_math_focus_keyword'], 255, '') : null,
                    'is_elementor' => $isElementor,
                    'status' => $post->post_status,
                    'published_at' => $this->safeDate($post->post_date_gmt),
                    'wp_modified_at' => $this->safeDate($post->post_modified_gmt),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $stats['processed']++;

                if (count($preview) < 30) {
                    $preview[] = [
                        $post->ID,
                        $this->groupMap[$post->ID] ?? '-',
                        $language,
                        Str::limit($slug, 50),
                        $isElementor ? 'YES' : 'no',
                        mb_strlen((string) $post->post_content).' chars',
                    ];
                }
            }

            if (! $this->option('dry-run') && $rows !== []) {
                Post::upsert(
                    $rows,
                    ['wp_post_id'],
                    [
                        'translation_group_id', 'language', 'slug', 'title', 'excerpt', 'body',
                        'featured_image', 'author', 'meta_title', 'meta_description', 'focus_keyword',
                        'is_elementor', 'status', 'published_at', 'wp_modified_at', 'updated_at',
                    ],
                );
            }

            $bar->advance(count($posts));
        });

        $bar->finish();
        $this->newLine(2);

        if ($preview !== []) {
            $this->line('Sample of processed posts:');
            $this->table(['WP ID', 'Group', 'Lang', 'Slug', 'Elementor', 'Body size'], $preview);
        }

        $this->table(['Metric', 'Count'], [
            ['Posts processed', $stats['processed']],
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
            $this->info('Import complete. Total posts in Laravel: '.Post::count());
        }

        return self::SUCCESS;
    }

    /**
     * Post IDs belonging to the first N translation groups that contain 2+ actual
     * blog posts (Polylang groups also cover pages — those are excluded here).
     */
    private function multilangGroupPostIds(ConnectionInterface $wp, int $groupCount, array $statuses): array
    {
        $groups = [];
        foreach ($this->groupMap as $postId => $groupId) {
            $groups[$groupId][] = $postId;
        }

        // Keep only IDs that are real posts (not pages) in an importable status.
        $validIds = $wp->table('posts')
            ->whereIn('ID', array_merge(...array_values($groups) ?: [[]]))
            ->where('post_type', 'post')
            ->whereIn('post_status', $statuses)
            ->pluck('ID')
            ->map(fn ($id) => (int) $id)
            ->flip()
            ->all();

        $multilang = [];
        foreach ($groups as $groupId => $ids) {
            $postsOnly = array_values(array_filter($ids, fn (int $id) => isset($validIds[$id])));
            if (count($postsOnly) >= 2) {
                $multilang[$groupId] = $postsOnly;
            }
        }
        ksort($multilang);

        $this->info('Multi-language groups containing 2+ blog posts: '.count($multilang).' total.');

        $postIds = [];
        foreach (array_slice($multilang, 0, $groupCount, true) as $ids) {
            $postIds = array_merge($postIds, $ids);
        }

        return $postIds;
    }

    /** Polylang stores each post's language via term_relationships -> term_taxonomy ('language') -> terms.slug */
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

    /**
     * Polylang stores translation groups as 'post_translations' terms whose description
     * is a PHP-serialized array: ['en' => 123, 'ar' => 456, ...].
     * We use the term_taxonomy_id as the group id.
     */
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

    /**
     * Resolve _thumbnail_id attachments to their relative uploads path
     * (wp_postmeta._wp_attached_file, e.g. "2023/08/fue-transplant.jpg").
     *
     * @return array<int,string> post_id => relative path
     */
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

    /**
     * Clean post_content:
     *  - rewrite WP media URLs to the R2 public URL (R2_PUBLIC_URL, e.g.
     *    https://media.turkeymed.net); a Cloudflare redirect covers old
     *    /wp-content/uploads/* URLs
     *  - strip Elementor widget headings that leaked into the content
     *  - collapse tab noise
     */
    private function cleanBody(?string $html): ?string
    {
        if (! $html) {
            return null;
        }

        $mediaBase = rtrim((string) config('filesystems.disks.r2.url'), '/');

        // Absolute URLs on our own domain only — external domains must stay untouched
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

        // Relative references like src="/wp-content/uploads/...". The lookbehind ensures
        // we never match the path inside another domain's absolute URL (e.g. example.com/wp-content/...).
        $html = preg_replace('~(?<![\w.])/wp-content/uploads/~', $mediaBase.'/', $html);

        // Elementor page furniture (form / related-posts widget headings), not article content
        $html = str_replace(
            ['<h3>Send Us A Message</h3>', '<h2>More Information</h2>'],
            '',
            $html,
        );

        $html = preg_replace('/\t+/', ' ', $html);

        return trim($html) ?: null;
    }

    /** WP uses '0000-00-00 00:00:00' for unset dates — convert to null. */
    private function safeDate(?string $date): ?string
    {
        return ($date && ! str_starts_with($date, '0000')) ? $date : null;
    }
}
