<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Service;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoMetaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_share_image_falls_back_to_the_brand_image_when_a_page_has_none(): void
    {
        $post = Post::factory()->create(); // no featured image

        $response = $this->get($post->url());

        $response->assertOk();
        $response->assertSee('property="og:image"', false);
        $response->assertSee('og-default.png', false);
        $response->assertSee('name="twitter:image"', false);
        $response->assertSee('name="twitter:title"', false);
        $response->assertSee('property="og:locale" content="en_US"', false);
    }

    public function test_admin_uploaded_og_image_overrides_the_default(): void
    {
        config(['filesystems.disks.r2.url' => 'https://media.test']);
        Setting::set('org.og_image', 'branding/share.png');

        $post = Post::factory()->create(); // no featured image of its own

        $this->get($post->url())->assertSee('https://media.test/branding/share.png', false);
    }

    public function test_icons_manifest_and_theme_color_are_linked(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('rel="manifest"', false);
        $response->assertSee('apple-touch-icon', false);
        $response->assertSee('name="theme-color"', false);
    }

    public function test_gtm_is_absent_by_default_and_present_when_configured(): void
    {
        config(['site.gtm_id' => '']);
        $this->get('/')->assertDontSee('googletagmanager.com/gtm.js', false);

        config(['site.gtm_id' => 'GTM-TEST123']);

        $response = $this->get('/');
        $response->assertSee('GTM-TEST123', false);
        $response->assertSee('googletagmanager.com/ns.html', false); // body noscript
    }

    public function test_search_engine_verification_tags_render_only_when_configured(): void
    {
        config(['site.google_site_verification' => '']);
        $this->get('/')->assertDontSee('google-site-verification', false);

        config(['site.google_site_verification' => 'verify-token-xyz']);

        $this->get('/')->assertSee('verify-token-xyz', false);
    }

    public function test_site_is_fully_noindexed_when_indexable_is_off(): void
    {
        config(['site.indexable' => false]);

        $response = $this->get('/');

        $response->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
        $response->assertSee('name="robots" content="noindex, nofollow"', false);
    }

    public function test_site_is_indexable_by_default(): void
    {
        config(['site.indexable' => true]);

        $this->get('/')->assertHeaderMissing('X-Robots-Tag');
    }

    public function test_templated_meta_title_is_trimmed_for_serps(): void
    {
        $post = Post::factory()->create(['title' => str_repeat('Hair Transplant ', 8), 'meta_title' => null]);

        $this->assertLessThanOrEqual(60, mb_strlen($post->metaTitle()));
    }

    public function test_meta_description_falls_back_to_clean_body_text(): void
    {
        $post = Post::factory()->create([
            'meta_description' => null,
            'summary' => null,
            'excerpt' => null,
            'body' => '<h2>Intro</h2><p>FUE hair transplant is a modern, scarless technique performed under local anaesthetic.</p>',
        ]);

        $description = $post->metaDescription();

        $this->assertNotNull($description);
        // Tags stripped, no raw HTML.
        $this->assertStringNotContainsString('<', $description);
        $this->assertStringContainsString('FUE hair transplant is a modern', $description);
        $this->assertLessThanOrEqual(155, mb_strlen($description));
    }

    public function test_meta_description_is_null_when_no_source_content(): void
    {
        $post = Post::factory()->create([
            'meta_description' => null,
            'summary' => null,
            'excerpt' => null,
            'body' => null,
        ]);

        $this->assertNull($post->metaDescription());
    }

    public function test_service_meta_description_also_falls_back_to_body(): void
    {
        $service = Service::factory()->create([
            'meta_description' => null,
            'summary' => null,
            'excerpt' => null,
            'body' => '<p>Gastric sleeve surgery reduces stomach size to support long-term weight loss.</p>',
        ]);

        $this->assertStringContainsString('Gastric sleeve surgery', (string) $service->metaDescription());
    }
}
