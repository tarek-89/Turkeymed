<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Redirect;
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
