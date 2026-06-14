<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImportWordPressRedirectsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_review_redirects_only_for_urls_that_no_longer_resolve(): void
    {
        Http::fake([
            'https://www.turkeymed.net/sitemap_index.xml' => Http::response(
                '<sitemapindex><sitemap><loc>https://www.turkeymed.net/post-sitemap.xml</loc></sitemap></sitemapindex>'
            ),
            'https://www.turkeymed.net/post-sitemap.xml' => Http::response(
                '<urlset>'
                .'<url><loc>https://www.turkeymed.net/kept-slug/</loc></url>'
                .'<url><loc>https://www.turkeymed.net/removed-slug/</loc></url>'
                .'<url><loc>https://www.turkeymed.net/category/old-category/</loc></url>'
                .'</urlset>'
            ),
        ]);

        Post::factory()->create(['slug' => 'kept-slug', 'language' => 'en']);

        $this->artisan('redirects:wordpress --apply')->assertSuccessful();

        // Slug preserved — the trailing-slash middleware handles it, no row.
        $this->assertDatabaseMissing('redirects', ['from_path' => 'kept-slug']);

        // Genuinely changed URLs are parked as inactive rows for review.
        $this->assertDatabaseHas('redirects', [
            'from_path' => 'removed-slug',
            'is_active' => false,
        ]);
        $this->assertDatabaseHas('redirects', [
            'from_path' => 'category/old-category',
            'is_active' => false,
        ]);
    }

    public function test_it_suggests_a_destination_from_the_leaf_slug_of_a_flattened_url(): void
    {
        Http::fake([
            'https://www.turkeymed.net/sitemap_index.xml' => Http::response(
                '<sitemapindex><sitemap><loc>https://www.turkeymed.net/page-sitemap.xml</loc></sitemap></sitemapindex>'
            ),
            'https://www.turkeymed.net/page-sitemap.xml' => Http::response(
                '<urlset><url><loc>https://www.turkeymed.net/weight-loss-procedures/gastric-bypass-surgery/</loc></url></urlset>'
            ),
        ]);

        $service = Service::factory()->create([
            'slug' => 'gastric-bypass-surgery',
            'language' => 'en',
        ]);

        $this->artisan('redirects:wordpress --apply')->assertSuccessful();

        $this->assertDatabaseHas('redirects', [
            'from_path' => 'weight-loss-procedures/gastric-bypass-surgery',
            'to_path' => $service->url(),
            'is_active' => false,
        ]);
    }

    public function test_a_dry_run_persists_nothing(): void
    {
        Http::fake([
            'https://www.turkeymed.net/sitemap_index.xml' => Http::response(
                '<sitemapindex><sitemap><loc>https://www.turkeymed.net/post-sitemap.xml</loc></sitemap></sitemapindex>'
            ),
            'https://www.turkeymed.net/post-sitemap.xml' => Http::response(
                '<urlset><url><loc>https://www.turkeymed.net/removed-slug/</loc></url></urlset>'
            ),
        ]);

        $this->artisan('redirects:wordpress')->assertSuccessful();

        $this->assertDatabaseCount('redirects', 0);
    }
}
