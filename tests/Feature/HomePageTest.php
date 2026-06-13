<?php

namespace Tests\Feature;

use App\Models\Gallery;
use App\Models\ProcessStep;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\TreatmentCard;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_homepage_renders_even_with_no_content(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('<main id="main"', false);
    }

    public function test_homepage_renders_hero_and_components(): void
    {
        Setting::set('home.hero_title', ['en' => 'World-class care']);
        Setting::set('home.hero_stat_value', '98%');
        Setting::set('home.hero_stat_label', ['en' => 'graft survival']);

        TreatmentCard::factory()->feature()->create(['title' => ['en' => 'Hair Transplant']]);
        Testimonial::factory()->create(['quote' => ['en' => 'Completely natural result'], 'author_name' => 'Markus W.']);
        ProcessStep::factory()->create(['title' => ['en' => 'Free consultation']]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('World-class care');
        $response->assertSee('98%');
        $response->assertSee('graft survival');
        $response->assertSee('Hair Transplant');
        $response->assertSee('Completely natural result');
        $response->assertSee('Free consultation');
    }

    public function test_unpublished_components_are_hidden(): void
    {
        TreatmentCard::factory()->unpublished()->create(['title' => ['en' => 'Hidden treatment']]);
        Testimonial::factory()->unpublished()->create(['quote' => ['en' => 'Hidden quote'], 'author_name' => 'Nobody']);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('Hidden treatment');
        $response->assertDontSee('Hidden quote');
    }

    public function test_video_embed_uses_nocookie_iframe(): void
    {
        Video::factory()->create(['youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ']);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('youtube-nocookie.com/embed/dQw4w9WgXcQ', false);
    }

    public function test_gallery_section_renders_published_galleries(): void
    {
        Gallery::factory()->create(['title' => ['en' => 'Clinic tour']]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Clinic tour');
    }

    public function test_localized_homepage_serves_arabic_rtl(): void
    {
        Setting::set('home.hero_title', ['en' => 'World-class care', 'ar' => 'رعاية عالمية']);

        $response = $this->get('/ar');

        $response->assertOk();
        $response->assertSee('رعاية عالمية');
        $response->assertSee('dir="rtl"', false);
    }

    public function test_blog_index_moved_to_blog_path(): void
    {
        $response = $this->get('/blog');

        $response->assertOk();
    }

    public function test_unknown_locale_homepage_returns_404(): void
    {
        $this->get('/zz')->assertNotFound();
    }
}
