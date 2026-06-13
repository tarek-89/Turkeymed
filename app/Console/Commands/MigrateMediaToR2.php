<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Uploads the legacy WordPress uploads folder to the Cloudflare R2 "media"
 * bucket at the bucket root: <year>/<month>/<file> (no WordPress naming).
 * Served via the bucket's custom domain, e.g. https://media.turkeymed.net/2025/02/x.jpg
 * (set R2_PUBLIC_URL accordingly).
 *
 * Old /wp-content/uploads/* URLs are handled by a Cloudflare redirect rule
 * so existing image links keep working.
 *
 * Idempotent: existing keys are skipped — safe to re-run / resume.
 *
 * Usage:
 *   php artisan media:migrate-to-r2 --dry-run        # list what would upload
 *   php artisan media:migrate-to-r2                  # upload everything
 *   php artisan media:migrate-to-r2 --only=2025/02   # one subfolder (for testing)
 */
class MigrateMediaToR2 extends Command
{
    protected $signature = 'media:migrate-to-r2
        {--dry-run : List what would be uploaded without uploading}
        {--only= : Restrict to a subfolder, e.g. 2025/02}
        {--source= : Source folder (default: storage/uploads)}
        {--prefix= : Optional key prefix in the bucket (default: bucket root)}';

    protected $description = 'Upload legacy WordPress uploads to Cloudflare R2, preserving paths';

    public function handle(): int
    {
        $source = rtrim($this->option('source') ?: storage_path('uploads'), '/');
        $prefix = trim((string) $this->option('prefix'), '/');
        $prefix = $prefix !== '' ? $prefix.'/' : '';
        $only = trim((string) $this->option('only'), '/');

        $scanRoot = $only ? $source.'/'.$only : $source;
        if (! is_dir($scanRoot)) {
            $this->error("Source folder not found: {$scanRoot}");

            return self::FAILURE;
        }

        $disk = Storage::disk('r2');

        // List existing bucket keys once (cheaper than exists() per file)
        $this->info('Listing existing objects in R2...');
        $existing = [];
        foreach ($disk->allFiles($prefix.$only) as $key) {
            $existing[$key] = true;
        }
        $this->info(count($existing).' objects already in bucket.');

        // Collect local files
        $files = [];
        $totalBytes = 0;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($scanRoot, RecursiveDirectoryIterator::SKIP_DOTS),
        );
        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (! $file->isFile() || str_starts_with($file->getFilename(), '.')) {
                continue;
            }
            $relative = ltrim(str_replace($source, '', $file->getPathname()), '/');
            $key = $prefix.$relative;
            if (isset($existing[$key])) {
                continue;
            }
            $files[] = [$file->getPathname(), $key, $file->getSize()];
            $totalBytes += $file->getSize();
        }

        $this->info(sprintf(
            '%d files to upload (%.1f MB)%s.',
            count($files),
            $totalBytes / 1024 / 1024,
            $this->option('dry-run') ? ' — DRY RUN' : '',
        ));

        if ($this->option('dry-run')) {
            foreach (array_slice($files, 0, 20) as [$path, $key]) {
                $this->line('  '.$key);
            }
            if (count($files) > 20) {
                $this->line('  ... and '.(count($files) - 20).' more');
            }

            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar(count($files));
        $failed = [];

        foreach ($files as [$path, $key]) {
            $stream = fopen($path, 'rb');
            $ok = $disk->writeStream($key, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
            if ($ok === false) {
                $failed[] = $key;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        if ($failed !== []) {
            $this->warn(count($failed).' uploads failed (re-run the command to retry):');
            foreach (array_slice($failed, 0, 20) as $key) {
                $this->line('  '.$key);
            }
        } else {
            $this->info('All uploads complete.');
        }

        return $failed === [] ? self::SUCCESS : self::FAILURE;
    }
}
