<?php

namespace Tests\Feature;

use App\Models\Promise;
use App\Models\Setting;
use App\Models\Stat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AboutPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_about_page_renders_settings_promises_and_stats(): void
    {
        Setting::set('about.heading', ['en' => 'Care you can trust']);
        Setting::set('about.text', ['en' => 'We are an Istanbul-based team.']);
        Setting::set('about.story_title', ['en' => 'A decade of care']);
        Setting::set('about.story_text', ['en' => "First paragraph.\n\nSecond paragraph."]);

        Promise::factory()->create(['title' => ['en' => 'Safety first'], 'text' => ['en' => 'Accredited only.']]);
        Stat::factory()->create(['value' => '15k+', 'label' => ['en' => 'Patients treated']]);

        $response = $this->get('/about');

        $response->assertOk();
        $response->assertSee('Care you can trust');
        $response->assertSee('We are an Istanbul-based team.');
        $response->assertSee('A decade of care');
        $response->assertSee('First paragraph.');
        $response->assertSee('Second paragraph.');
        $response->assertSee('Safety first');
        $response->assertSee('15k+');
        $response->assertSee('Patients treated');
    }

    public function test_about_page_hides_unpublished_promises_and_stats(): void
    {
        Setting::set('about.heading', ['en' => 'About us']);

        Promise::factory()->unpublished()->create(['title' => ['en' => 'Hidden promise']]);
        Stat::factory()->unpublished()->create(['label' => ['en' => 'Hidden stat']]);

        $response = $this->get('/about');

        $response->assertOk();
        $response->assertDontSee('Hidden promise');
        $response->assertDontSee('Hidden stat');
    }

    public function test_about_page_serves_arabic_translation_with_rtl(): void
    {
        Setting::set('about.heading', ['en' => 'Care you can trust', 'ar' => 'رعاية تثق بها']);

        $response = $this->get('/ar/about');

        $response->assertOk();
        $response->assertSee('رعاية تثق بها');
        $response->assertSee('dir="rtl"', false);
    }

    public function test_unknown_locale_prefix_returns_404(): void
    {
        $response = $this->get('/zz/about');

        $response->assertNotFound();
    }

    public function test_promises_and_stats_respect_sort_order(): void
    {
        $second = Promise::factory()->create(['title' => ['en' => 'Second'], 'sort_order' => 5]);
        $first = Promise::factory()->create(['title' => ['en' => 'First'], 'sort_order' => 1]);

        $response = $this->get('/about');

        $response->assertOk();
        $response->assertSeeInOrder(['First', 'Second']);
    }
}
