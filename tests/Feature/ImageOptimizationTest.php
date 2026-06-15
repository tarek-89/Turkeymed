<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Setting;
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

    public function test_homepage_hero_is_preloaded_with_dimensions_and_srcset(): void
    {
        config(['filesystems.disks.r2.url' => 'https://media.example.com']);

        Setting::set('home.hero_title', ['en' => 'World-class care']);
        Setting::set('home.hero_images', ['home/hero/a.jpg']);
        Setting::set('home.hero_images_meta', [
            'home/hero/a.jpg' => [
                'width' => 1600,
                'height' => 1000,
                'variants' => [400 => 'home/hero/a-400.webp', 800 => 'home/hero/a-800.webp'],
            ],
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('rel="preload" as="image"', false);
        $response->assertSee('https://media.example.com/home/hero/a.jpg', false);
        $response->assertSee('width="1600"', false);
        $response->assertSee('height="1000"', false);
        $response->assertSee('https://media.example.com/home/hero/a-800.webp 800w', false);
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
