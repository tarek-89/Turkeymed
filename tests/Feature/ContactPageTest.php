<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function seedHero(): void
    {
        Setting::set('contact.hero_title', ['en' => 'Talk to a coordinator today']);
        Setting::set('contact.hero_text', ['en' => 'Reach us however suits you.']);
    }

    public function test_contact_page_renders_hero_and_offices(): void
    {
        $this->seedHero();
        Office::factory()->create([
            'country' => ['en' => 'Türkiye'],
            'name' => ['en' => 'Istanbul · Şişli'],
            'address' => ['en' => 'Büyükdere Cd. No: 000'],
            'phone' => '+90 212 000 00 00',
        ]);

        $response = $this->get('/contact');

        $response->assertOk();
        $response->assertSee('Talk to a coordinator today');
        $response->assertSee('Türkiye');
        $response->assertSee('Istanbul · Şişli');
        $response->assertSee('+90 212 000 00 00');
    }

    public function test_offices_are_grouped_by_country_with_a_count(): void
    {
        $this->seedHero();
        Office::factory()->count(2)->create(['country' => ['en' => 'Türkiye'], 'sort_order' => 1]);
        Office::factory()->create(['country' => ['en' => 'United Kingdom'], 'sort_order' => 3]);

        $response = $this->get('/contact');

        $response->assertOk();
        $response->assertSee(trans_choice('contact.office_count', 2, ['count' => 2]));
        $response->assertSee(trans_choice('contact.office_count', 1, ['count' => 1]));
    }

    public function test_unpublished_offices_are_hidden(): void
    {
        $this->seedHero();
        Office::factory()->unpublished()->create(['name' => ['en' => 'Secret office']]);

        $response = $this->get('/contact');

        $response->assertOk();
        $response->assertDontSee('Secret office');
    }

    public function test_form_embed_is_rendered_when_set(): void
    {
        $this->seedHero();
        Setting::set('contact.form_embed', '<iframe src="https://forms.example.com/abc" title="enquiry"></iframe>');

        $response = $this->get('/contact');

        $response->assertOk();
        $response->assertSee('https://forms.example.com/abc', false);
    }

    public function test_map_embed_is_rendered_when_set(): void
    {
        $this->seedHero();
        Setting::set('contact.map_embed', '<iframe src="https://maps.google.com/embed?xyz"></iframe>');

        $response = $this->get('/contact');

        $response->assertOk();
        $response->assertSee('https://maps.google.com/embed?xyz', false);
    }

    public function test_localized_contact_page_serves_arabic_rtl(): void
    {
        Setting::set('contact.hero_title', ['en' => 'Talk to us', 'ar' => 'تحدث إلينا']);

        $response = $this->get('/ar/contact');

        $response->assertOk();
        $response->assertSee('تحدث إلينا');
        $response->assertSee('dir="rtl"', false);
    }

    public function test_unknown_locale_prefix_returns_404(): void
    {
        $response = $this->get('/zz/contact');

        $response->assertNotFound();
    }
}
