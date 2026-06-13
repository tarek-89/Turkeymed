<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\SocialLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialLinkTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Setting::set('contact.hero_title', ['en' => 'Talk to us']);
    }

    public function test_published_social_links_appear_on_contact_page(): void
    {
        SocialLink::factory()->platform('instagram')->create(['url' => 'https://instagram.com/turkeymed']);

        $response = $this->get('/contact');

        $response->assertOk();
        $response->assertSee(__('contact.follow_us'));
        $response->assertSee('https://instagram.com/turkeymed', false);
        $response->assertSee('Instagram', false); // accessible label fallback
    }

    public function test_unpublished_social_links_are_hidden(): void
    {
        SocialLink::factory()->platform('facebook')->unpublished()->create(['url' => 'https://facebook.com/hidden']);

        $response = $this->get('/contact');

        $response->assertOk();
        $response->assertDontSee('https://facebook.com/hidden', false);
    }

    public function test_custom_label_overrides_the_platform_name(): void
    {
        $link = SocialLink::factory()->platform('website')->create([
            'url' => 'https://blog.example.com',
            'label' => 'Read our blog',
        ]);

        $this->assertSame('Read our blog', $link->displayLabel());
    }

    public function test_display_label_falls_back_to_platform_name(): void
    {
        $link = SocialLink::factory()->platform('youtube')->create(['label' => null]);

        $this->assertSame('YouTube', $link->displayLabel());
    }
}
