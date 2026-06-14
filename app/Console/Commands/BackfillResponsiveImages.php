<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\Service;
use App\Support\Images\ResponsiveImageGenerator;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class BackfillResponsiveImages extends Command
{
    protected $signature = 'images:variants {--force : Regenerate even when metadata already exists}';

    protected $description = 'Generate responsive WebP variants for existing post/service featured images.';

    public function handle(ResponsiveImageGenerator $generator): int
    {
        if (blank(config('filesystems.disks.r2.key'))) {
            $this->error('R2 is not configured (R2_ACCESS_KEY_ID is empty). Aborting.');

            return self::FAILURE;
        }

        $disk = Storage::disk('r2');
        $force = (bool) $this->option('force');
        $count = 0;

        foreach ([Post::class, Service::class] as $model) {
            $model::query()
                ->whereNotNull('featured_image')
                ->when(! $force, fn ($query) => $query->whereNull('featured_image_meta'))
                ->chunkById(100, function (Collection $rows) use ($generator, $disk, &$count): void {
                    foreach ($rows as $row) {
                        $meta = $generator->generate($disk, (string) $row->featured_image);
                        $row->forceFill(['featured_image_meta' => $meta])->saveQuietly();
                        $count++;
                    }
                });
        }

        $this->info("Processed {$count} image(s).");

        return self::SUCCESS;
    }
}
