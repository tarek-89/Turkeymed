<?php

namespace Tests\Feature;

use App\Models\Redirect;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FixWeightLossCategoryTest extends TestCase
{
    use RefreshDatabase;

    private function seedMisfiledData(): array
    {
        $bad = ServiceCategory::factory()->create(['name' => 'Services', 'slug' => 'services']);
        $target = ServiceCategory::factory()->create(['name' => 'Weight Loss', 'slug' => 'weight-loss']);

        $service = Service::factory()->inCategory($bad)->create([
            'title' => 'Gastric Sleeve Surgery',
            'slug' => 'gastric-sleeve-surgery',
            'language' => 'en',
        ]);

        return [$bad, $target, $service];
    }

    public function test_it_moves_services_and_deletes_the_bad_category(): void
    {
        [$bad, $target, $service] = $this->seedMisfiledData();

        $this->artisan('services:fix-weight-loss-category')->assertSuccessful();

        $this->assertDatabaseMissing('service_categories', ['id' => $bad->id]);
        $this->assertSame($target->id, $service->fresh()->service_category_id);
    }

    public function test_it_creates_a_301_redirect_from_old_to_new_url(): void
    {
        [, , $service] = $this->seedMisfiledData();

        $oldUrl = $service->url(); // captured while still in the "services" category

        $this->artisan('services:fix-weight-loss-category')->assertSuccessful();

        $newUrl = $service->fresh()->url();

        $this->assertNotSame($oldUrl, $newUrl);
        $this->assertDatabaseHas('redirects', [
            'from_path' => Redirect::normalizePath(parse_url($oldUrl, PHP_URL_PATH)),
            'to_path' => parse_url($newUrl, PHP_URL_PATH),
            'status_code' => 301,
            'is_active' => true,
        ]);
    }

    public function test_dry_run_changes_nothing(): void
    {
        [$bad, , $service] = $this->seedMisfiledData();

        $this->artisan('services:fix-weight-loss-category --dry-run')->assertSuccessful();

        $this->assertDatabaseHas('service_categories', ['id' => $bad->id]);
        $this->assertSame($bad->id, $service->fresh()->service_category_id);
        $this->assertDatabaseCount('redirects', 0);
    }

    public function test_it_is_idempotent_when_source_category_is_absent(): void
    {
        ServiceCategory::factory()->create(['slug' => 'weight-loss']);

        // No "services" category exists — command should no-op successfully.
        $this->artisan('services:fix-weight-loss-category')->assertSuccessful();
    }

    public function test_it_fails_when_target_category_missing(): void
    {
        ServiceCategory::factory()->create(['name' => 'Services', 'slug' => 'services']);

        $this->artisan('services:fix-weight-loss-category')->assertFailed();
    }
}
