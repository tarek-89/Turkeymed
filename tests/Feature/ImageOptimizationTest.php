<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImageOptimizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_post_hero_has_intrinsic_dimensions_and_is_preloaded(): void
    {
        $post = Post::factory()->withFeaturedImage()->create();

        $response = $this->get($post->url());

        $response->assertOk();
        $response->assertSee('width="2100"', false);
        $response->assertSee('height="900"', false);
        $response->assertSee('fetchpriority="high"', false);
        $response->assertSee('rel="preload" as="image"', false);
    }

    public function test_blog_cards_carry_image_dimensions(): void
    {
        Post::factory()->withFeaturedImage()->create();

        $response = $this->get('/blog');

        $response->assertOk();
        $response->assertSee('width="1600"', false);
        $response->assertSee('height="1000"', false);
        $response->assertSee('loading="lazy"', false);
    }
}
