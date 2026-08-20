<?php

namespace App\Console\Commands;

use App\Models\Redirect;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * One-off data cleanup: an import artifact filed the weight-loss treatments
 * under a mislabeled "Services" category (slug "services"), while a correctly
 * named but empty "Weight Loss" category (slug "weight-loss") also exists.
 *
 * This moves every service from the "services" category into "weight-loss",
 * creates a 301 redirect from each old /services/services/{slug} URL to its
 * new /services/weight-loss/{slug} URL, and deletes the now-empty "services"
 * category. Idempotent and safe to re-run; supports --dry-run.
 */
class FixWeightLossCategory extends Command
{
    protected $signature = 'services:fix-weight-loss-category
        {--from=services : Slug of the mislabeled source category}
        {--to=weight-loss : Slug of the correct target category}
        {--dry-run : Report what would change without writing anything}';

    protected $description = 'Move weight-loss treatments out of the mislabeled "Services" category into "Weight Loss", with 301 redirects.';

    public function handle(): int
    {
        $fromSlug = (string) $this->option('from');
        $toSlug = (string) $this->option('to');
        $dryRun = (bool) $this->option('dry-run');

        $from = ServiceCategory::where('slug', $fromSlug)->first();
        $to = ServiceCategory::where('slug', $toSlug)->first();

        if (! $from) {
            $this->info("Source category \"{$fromSlug}\" not found — nothing to do (already fixed?).");

            return self::SUCCESS;
        }

        if (! $to) {
            $this->error("Target category \"{$toSlug}\" does not exist. Create it first, then re-run.");

            return self::FAILURE;
        }

        $services = $from->services()->get();

        if ($services->isEmpty()) {
            $this->warn("Source category \"{$fromSlug}\" has no services. It will just be deleted.");
        }

        $this->info(($dryRun ? '[DRY RUN] ' : '')."Moving {$services->count()} service(s) from \"{$fromSlug}\" to \"{$toSlug}\":");

        // Capture the old URL BEFORE reassigning — url() depends on the category.
        $moves = $services->map(fn (Service $service): array => [
            'service' => $service,
            'old_url' => $service->url(),
        ])->all();

        if ($dryRun) {
            foreach ($moves as $move) {
                /** @var Service $service */
                $service = $move['service'];
                $newUrl = $this->previewNewUrl($service, $to);
                $this->line("  • [{$service->language}] {$service->title}");
                $this->line("      {$move['old_url']}  ->  {$newUrl}");
            }
            $this->info('[DRY RUN] '.$from->services()->count().' service(s) would move; category "'.$fromSlug.'" would be deleted. No changes made.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($moves, $to, $from): void {
            foreach ($moves as $move) {
                /** @var Service $service */
                $service = $move['service'];

                $service->service_category_id = $to->id;
                $service->save();

                $newUrl = $service->fresh()->url();

                $this->createRedirect($move['old_url'], $newUrl);

                $this->line("  moved: [{$service->language}] {$service->title}");
            }

            $from->delete();
        });

        $this->info("Done. Category \"{$fromSlug}\" removed; services now under \"{$toSlug}\" with 301 redirects.");

        return self::SUCCESS;
    }

    /**
     * Create (or refresh) a 301 redirect from the old path to the new one,
     * skipping self-redirects. Idempotent via updateOrCreate on from_path.
     */
    private function createRedirect(string $oldUrl, string $newUrl): void
    {
        $from = Redirect::normalizePath(parse_url($oldUrl, PHP_URL_PATH) ?: '');
        $to = parse_url($newUrl, PHP_URL_PATH) ?: '/';

        if ($from === '' || $from === Redirect::normalizePath($to)) {
            return;
        }

        Redirect::updateOrCreate(
            ['from_path' => $from],
            ['to_path' => $to, 'status_code' => 301, 'is_active' => true],
        );
    }

    /**
     * The URL a service would have under the target category, without saving.
     */
    private function previewNewUrl(Service $service, ServiceCategory $to): string
    {
        $original = $service->service_category_id;
        $service->service_category_id = $to->id;
        $service->setRelation('category', $to);
        $url = $service->url();
        $service->service_category_id = $original;

        return $url;
    }
}
