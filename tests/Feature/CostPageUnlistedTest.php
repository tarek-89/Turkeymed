<?php

namespace Tests\Feature;

use App\Models\CostPage;
use App\Support\Cost\SectionKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CostPageUnlistedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_unlisted_page_is_noindex_and_kept_out_of_footer_sitemap_and_llms(): void
    {
        $page = CostPage::factory()->create(['is_unlisted' => true, 'title' => ['en' => 'Private offer page', 'fr' => 'Offre privée']]);

        $response = $this->get('/pricing/'.$page->slug);

        $response->assertOk();
        $response->assertSee('Private offer page');
        $response->assertSee('<meta name="robots" content="noindex, nofollow">', false);
        $response->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive, noai, noimageai');
        $response->assertDontSee('<link rel="alternate" hreflang=', false);

        $this->get('/about')->assertOk()->assertDontSee('Private offer page');
        $this->get('/sitemap.xml')->assertOk()->assertDontSee($page->url('en'), false);
        $this->get('/llms.txt')->assertOk()->assertDontSee($page->url('en'), false);
    }

    public function test_listed_page_is_indexable_and_linked(): void
    {
        $page = CostPage::factory()->create(['title' => ['en' => 'Public pricing page']]);

        $response = $this->get('/pricing/'.$page->slug);

        $response->assertOk();
        $response->assertDontSee('name="robots"', false);
        $response->assertHeaderMissing('X-Robots-Tag');

        $this->get('/about')->assertOk()->assertSee('Public pricing page');
    }

    public function test_access_key_is_required_when_set(): void
    {
        $page = CostPage::factory()->create(['is_unlisted' => true, 'access_key' => 'abc123XYZ', 'title' => ['en' => 'Keyed page', 'ar' => 'صفحة خاصة']]);

        $this->get('/pricing/'.$page->slug)->assertNotFound();
        $this->get('/pricing/'.$page->slug.'?key=wrong')->assertNotFound();
        $this->get('/pricing/'.$page->slug.'?key=abc123XYZ')->assertOk()->assertSee('Keyed page');
        $this->get('/ar/pricing/'.$page->slug.'?key=abc123XYZ')->assertOk()->assertSee('صفحة خاصة');

        $this->assertSame($page->url('en').'?key=abc123XYZ', $page->shareUrl('en'));

        // A key on a listed page is ignored.
        $page->update(['is_unlisted' => false]);
        $this->get('/pricing/'.$page->slug)->assertOk();
    }

    public function test_page_keeps_an_h1_when_the_calculator_section_is_hidden(): void
    {
        $page = CostPage::factory()->create(['title' => ['en' => 'Dental implant cost in Turkey']]);
        $page->section(SectionKey::Calculator)->update(['is_visible' => false]);

        $response = $this->get('/pricing/'.$page->slug);

        $response->assertOk();
        $this->assertSame(1, substr_count($response->getContent(), '<h1'));
        $response->assertSee('Dental implant cost in Turkey');
        $response->assertDontSee('data-graft-calculator', false);
    }
}
