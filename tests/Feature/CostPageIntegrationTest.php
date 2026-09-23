<?php

namespace Tests\Feature;

use App\Models\CostPage;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CostPageIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_published_pricing_pages_are_linked_in_the_footer_per_locale(): void
    {
        $page = CostPage::factory()->create(['title' => ['en' => 'Hair transplant cost in Turkey', 'fr' => 'Coût de la greffe de cheveux']]);
        CostPage::factory()->unpublished()->create(['title' => ['en' => 'Draft pricing page']]);

        $this->get('/about')
            ->assertOk()
            ->assertSee('Hair transplant cost in Turkey')
            ->assertSee($page->url('en'), false)
            ->assertDontSee('Draft pricing page');

        $this->get('/fr/about')
            ->assertOk()
            ->assertSee('Coût de la greffe de cheveux')
            ->assertSee($page->url('fr'), false);
    }

    public function test_sitemap_and_llms_list_published_pricing_pages_with_alternates(): void
    {
        $page = CostPage::factory()->create(['title' => ['en' => 'Hair transplant cost in Turkey', 'ar' => 'تكلفة زراعة الشعر']]);
        $draft = CostPage::factory()->unpublished()->create();

        $sitemap = $this->get('/sitemap.xml');
        $sitemap->assertOk();
        $sitemap->assertSee('<loc>'.$page->url('en').'</loc>', false);
        $sitemap->assertSee('<loc>'.$page->url('ar').'</loc>', false);
        $sitemap->assertSee('hreflang="ar" href="'.$page->url('ar').'"', false);
        $sitemap->assertDontSee($draft->url('en'), false);
        $sitemap->assertDontSee($page->url('fr'), false);

        $llms = $this->get('/llms.txt');
        $llms->assertOk();
        $llms->assertSee('['.'Hair transplant cost in Turkey]('.$page->url('en').')', false);
        $llms->assertSee($page->url('ar'), false);
        $llms->assertDontSee($page->url('fr'), false);
    }

    public function test_schema_markup_carries_the_price_range(): void
    {
        $category = ServiceCategory::factory()->create(['name' => ['en' => 'Hair Transplant Surgery']]);
        $page = CostPage::factory()->inCategory($category)->create(['price_range_min' => 1500, 'price_range_max' => 3200, 'currency' => 'EUR']);

        $response = $this->get('/pricing/'.$page->slug);

        $response->assertOk();
        $response->assertSee('"@type":"MedicalWebPage"', false);
        $response->assertSee('"@type":"AggregateOffer","priceCurrency":"EUR","lowPrice":1500,"highPrice":3200', false);
        $response->assertSee('"serviceType":"Hair Transplant Surgery"', false);
        $response->assertSee('"@type":"BreadcrumbList"', false);
    }
}
