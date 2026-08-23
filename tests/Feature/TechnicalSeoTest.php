<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Redirect;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TechnicalSeoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_rss_feed_lists_latest_posts(): void
    {
        $post = Post::factory()->create(['title' => 'My Feed Post']);

        $response = $this->get('/feed.xml');

        $response->assertOk();
        $this->assertStringContainsString('application/rss+xml', (string) $response->headers->get('Content-Type'));
        $response->assertSee('<rss', false);
        $response->assertSee('My Feed Post', false);
        $response->assertSee($post->url(), false);
    }

    public function test_sitemap_includes_featured_images(): void
    {
        $post = Post::factory()->withFeaturedImage()->create();

        $response = $this->get('/sitemap.xml');

        $response->assertSee('<image:image>', false);
        $response->assertSee($post->featuredImageUrl(), false);
    }

    public function test_robots_txt_blocks_everything_when_not_indexable(): void
    {
        config(['site.indexable' => false]);

        $response = $this->get('/robots.txt');

        $response->assertOk();
        $this->assertStringContainsString('text/plain', (string) $response->headers->get('Content-Type'));
        $response->assertSee('Disallow: /', false);
        $response->assertDontSee('Sitemap:', false);
    }

    public function test_robots_txt_allows_crawling_and_advertises_sitemap_when_indexable(): void
    {
        config(['site.indexable' => true]);

        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertSee('Disallow: /admin', false);
        $response->assertSee('Sitemap: '.url('/sitemap.xml'), false);
    }

    public function test_llms_txt_lists_key_pages_and_categorised_services_when_indexable(): void
    {
        config(['site.indexable' => true]);
        $category = ServiceCategory::factory()->create(['name' => ['en' => 'Hair Transplant'], 'slug' => 'hair-transplant']);
        $service = Service::factory()->inCategory($category)->create(['title' => 'FUE Hair Transplant']);

        $response = $this->get('/llms.txt');

        $response->assertOk();
        $this->assertStringContainsString('text/plain', (string) $response->headers->get('Content-Type'));
        // H1 + curated content.
        $response->assertSee('# '.config('site.brand'), false);
        $response->assertSee('## English', false);
        $response->assertSee('FUE Hair Transplant', false);
        // Absolute URLs (spec requires full URLs, not relative paths).
        $response->assertSee(url('/services'), false);
        $response->assertSee($service->url(), false);
    }

    public function test_llms_txt_excludes_uncategorised_junk_pages(): void
    {
        config(['site.indexable' => true]);
        // Legal/utility pages are imported as uncategorised services — they
        // must not be advertised to AI as treatments.
        Service::factory()->create(['title' => 'Privacy Policy', 'service_category_id' => null]);

        $response = $this->get('/llms.txt');

        $response->assertOk();
        $response->assertDontSee('Privacy Policy', false);
    }

    public function test_llms_txt_decodes_html_entities_in_titles(): void
    {
        config(['site.indexable' => true]);
        $category = ServiceCategory::factory()->create(['name' => ['en' => 'Dental']]);
        Service::factory()->inCategory($category)->create(['title' => 'Terms &amp; Conditions Review']);

        $response = $this->get('/llms.txt');

        $response->assertOk();
        $response->assertSee('Terms & Conditions Review', false);
        $response->assertDontSee('Terms &amp; Conditions Review', false);
    }

    public function test_llms_txt_includes_all_supported_languages(): void
    {
        config(['site.indexable' => true]);

        $response = $this->get('/llms.txt');

        $response->assertOk();
        $response->assertSee('## Français', false);
        $response->assertSee('## Español', false);
        $response->assertSee(url('/fr/services'), false);
    }

    public function test_llms_txt_uses_custom_summary_when_set(): void
    {
        config(['site.indexable' => true]);
        Setting::set('llms.summary', 'A bespoke one-line description of the clinic.');

        $response = $this->get('/llms.txt');

        $response->assertOk();
        $response->assertSee('A bespoke one-line description of the clinic.', false);
    }

    public function test_llms_txt_is_minimal_when_not_indexable(): void
    {
        config(['site.indexable' => false]);
        Service::factory()->create(['title' => 'Hidden Service']);

        $response = $this->get('/llms.txt');

        $response->assertOk();
        $response->assertSee('not yet public', false);
        $response->assertDontSee('Hidden Service', false);
    }

    public function test_paginated_blog_self_canonicalises(): void
    {
        Post::factory()->count(15)->create();

        $response = $this->get('/blog?page=2');

        $response->assertOk();
        $response->assertSee('rel="canonical" href="'.url('/blog').'?page=2"', false);
    }

    public function test_redirect_check_detects_a_chain(): void
    {
        Redirect::factory()->create(['from_path' => 'a', 'to_path' => '/b', 'is_active' => true]);
        Redirect::factory()->create(['from_path' => 'b', 'to_path' => '/c', 'is_active' => true]);

        $this->artisan('redirects:check')->assertFailed();
    }

    public function test_redirect_check_passes_when_redirects_are_direct(): void
    {
        Redirect::factory()->create(['from_path' => 'a', 'to_path' => '/final-destination', 'is_active' => true]);

        $this->artisan('redirects:check')->assertSuccessful();
    }
}
