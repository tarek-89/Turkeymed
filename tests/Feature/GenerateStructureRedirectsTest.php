<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateStructureRedirectsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_it_creates_active_301s_from_old_flat_urls(): void
    {
        $category = ServiceCategory::factory()->create(['slug' => 'hair-transplant-surgery']);
        Post::factory()->inCategory($category)->create(['slug' => 'why-dhi', 'language' => 'en']);
        Service::factory()->inCategory($category)->create(['slug' => 'dhi-technique', 'language' => 'en']);

        $this->artisan('redirects:restructure --apply')->assertSuccessful();

        $this->assertDatabaseHas('redirects', [
            'from_path' => 'why-dhi',
            'to_path' => '/blog/hair-transplant-surgery/why-dhi',
            'status_code' => 301,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('redirects', [
            'from_path' => 'dhi-technique',
            'to_path' => '/services/hair-transplant-surgery/dhi-technique',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('redirects', [
            'from_path' => 'category/hair-transplant-surgery',
            'to_path' => '/services/hair-transplant-surgery',
        ]);
    }

    public function test_localized_old_urls_keep_their_locale_prefix(): void
    {
        $category = ServiceCategory::factory()->create(['slug' => 'hair-transplant-surgery']);
        Post::factory()->inCategory($category)->create(['slug' => 'pourquoi-dhi', 'language' => 'fr']);

        $this->artisan('redirects:restructure --apply')->assertSuccessful();

        $this->assertDatabaseHas('redirects', [
            'from_path' => 'fr/pourquoi-dhi',
            'to_path' => '/fr/blog/hair-transplant-surgery/pourquoi-dhi',
        ]);
    }

    public function test_the_generated_redirect_is_followed_end_to_end(): void
    {
        $category = ServiceCategory::factory()->create(['slug' => 'hair-transplant-surgery']);
        Post::factory()->inCategory($category)->create(['slug' => 'why-dhi', 'language' => 'en']);

        $this->artisan('redirects:restructure --apply')->assertSuccessful();

        $this->get('/why-dhi')
            ->assertStatus(301)
            ->assertRedirect('/blog/hair-transplant-surgery/why-dhi');
    }

    public function test_a_dry_run_persists_nothing(): void
    {
        ServiceCategory::factory()->create(['slug' => 'hair-transplant-surgery']);

        $this->artisan('redirects:restructure')->assertSuccessful();

        $this->assertDatabaseCount('redirects', 0);
    }
}
