<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_is_served_as_xml(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $this->assertStringContainsString('application/xml', (string) $response->headers->get('Content-Type'));
        $response->assertSee('<urlset', false);
    }

    public function test_sitemap_lists_published_content_only(): void
    {
        $post = Post::factory()->create();
        $draft = Post::factory()->draft()->create();
        $service = Service::factory()->create();

        $response = $this->get('/sitemap.xml');

        $response->assertSee($post->url(), false);
        $response->assertSee($service->url(), false);
        $response->assertDontSee($draft->url(), false);
    }

    public function test_sitemap_advertises_hreflang_alternates_for_translations(): void
    {
        Post::factory()->inTranslationGroup(99)->create();
        $french = Post::factory()->language('fr')->inTranslationGroup(99)->create();

        $response = $this->get('/sitemap.xml');

        $response->assertSee('hreflang="fr"', false);
        $response->assertSee($french->url(), false);
    }

    public function test_sitemap_includes_service_categories(): void
    {
        $category = ServiceCategory::factory()->create();

        $this->get('/sitemap.xml')->assertSee($category->url(), false);
    }
}
