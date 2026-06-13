<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Support\Locale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function makePost(string $language, string $slug): Post
    {
        return Post::create([
            'language' => $language,
            'slug' => $slug,
            'title' => ucfirst($language).' article',
            'body' => '<p>Body</p>',
            'status' => 'publish',
            'published_at' => now()->subDay(),
        ]);
    }

    public function test_english_content_is_served_unprefixed_and_ltr(): void
    {
        $this->makePost('en', 'hello-world');

        $response = $this->get('/hello-world');

        $response->assertOk();
        $response->assertSee('lang="en"', false);
        $response->assertSee('dir="ltr"', false);
    }

    public function test_french_prefix_sets_french_locale_and_translations(): void
    {
        $this->makePost('fr', 'bonjour');

        $response = $this->get('/fr/bonjour');

        $response->assertOk();
        $response->assertSee('lang="fr"', false);
        $response->assertSee('dir="ltr"', false);
        // Header CTA is translated from lang/fr/nav.json.
        $response->assertSee('Consultation gratuite');
    }

    public function test_arabic_prefix_sets_rtl_direction(): void
    {
        $this->makePost('ar', 'marhaba');

        $response = $this->get('/ar/marhaba');

        $response->assertOk();
        $response->assertSee('lang="ar"', false);
        $response->assertSee('dir="rtl"', false);
        $response->assertSee('استشارة مجانية');
    }

    public function test_translations_resolve_per_locale_from_categorised_json(): void
    {
        app()->setLocale('en');
        $this->assertSame('Free consultation', __('nav.cta'));

        app()->setLocale('fr');
        $this->assertSame('Consultation gratuite', __('nav.cta'));

        app()->setLocale('es');
        $this->assertSame('Consulta gratuita', __('nav.cta'));

        app()->setLocale('ar');
        $this->assertSame('استشارة مجانية', __('nav.cta'));
    }

    public function test_switch_url_swaps_the_locale_prefix(): void
    {
        $this->app->instance('request', Request::create('/fr/foo', 'GET'));

        $this->assertStringEndsWith('/es/foo', Locale::switchUrl('es'));
        $this->assertStringEndsWith('/fr/foo', Locale::switchUrl('fr'));
        // English (default) drops the prefix.
        $this->assertStringEndsWith('/foo', Locale::switchUrl('en'));
    }

    public function test_direction_helper(): void
    {
        $this->assertSame('ltr', Locale::direction('en'));
        $this->assertSame('rtl', Locale::direction('ar'));
    }

    public function test_language_switcher_links_to_the_translated_slug(): void
    {
        $en = Post::factory()->inTranslationGroup(10)->create(['slug' => 'hair-implant-cost-turkey']);
        $ar = Post::factory()->language('ar')->inTranslationGroup(10)->create(['slug' => 'تكلفة-زراعة-الشعر']);

        $response = $this->get('/hair-implant-cost-turkey');

        $response->assertOk();
        // Links to the actual Arabic slug, not a naive prefix swap (which would 404).
        $response->assertSee($ar->url(), false);
        $response->assertDontSee(url('/ar/hair-implant-cost-turkey'), false);
    }

    public function test_language_switcher_omits_languages_without_a_translation(): void
    {
        Post::factory()->inTranslationGroup(11)->create(['slug' => 'only-english']);

        $response = $this->get('/only-english');

        $response->assertOk();
        $response->assertDontSee(url('/fr/only-english'), false);
        $response->assertDontSee(url('/es/only-english'), false);
    }
}
