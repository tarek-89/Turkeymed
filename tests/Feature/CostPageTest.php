<?php

namespace Tests\Feature;

use App\Models\CostPage;
use App\Models\ServiceCategory;
use App\Support\Cost\SectionKey;
use Database\Seeders\CostPageSeeder;
use Database\Seeders\ServiceCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CostPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_pricing_page_renders_in_the_default_language(): void
    {
        $page = CostPage::factory()->create(['title' => ['en' => 'Hair transplant cost in Turkey']]);

        $response = $this->get('/pricing/'.$page->slug);

        $response->assertOk();
        $response->assertSee('Hair transplant cost in Turkey');
        $response->assertSee('<html lang="en"', false);
    }

    public function test_pricing_page_renders_localized_with_fallback_to_english(): void
    {
        $page = CostPage::factory()->create(['title' => ['en' => 'Hair transplant cost in Turkey', 'ar' => 'تكلفة زراعة الشعر في تركيا']]);

        $this->get('/ar/pricing/'.$page->slug)
            ->assertOk()
            ->assertSee('تكلفة زراعة الشعر في تركيا')
            ->assertSee('dir="rtl"', false);

        // French has no translation: the English title is used.
        $this->get('/fr/pricing/'.$page->slug)
            ->assertOk()
            ->assertSee('Hair transplant cost in Turkey');
    }

    public function test_unpublished_page_and_unknown_locale_return_404(): void
    {
        $page = CostPage::factory()->unpublished()->create();

        $this->get('/pricing/'.$page->slug)->assertNotFound();
        $this->get('/pricing/does-not-exist')->assertNotFound();

        $published = CostPage::factory()->create();
        $this->get('/xx/pricing/'.$published->slug)->assertNotFound();
    }

    public function test_every_section_row_is_created_with_the_page(): void
    {
        $page = CostPage::factory()->create();

        $this->assertSame(
            SectionKey::values(),
            $page->sections()->orderBy('sort_order')->pluck('key')->all(),
        );
    }

    public function test_hidden_sections_are_not_rendered_and_visible_ones_follow_sort_order(): void
    {
        $page = CostPage::factory()->create();

        // Promise and CTA render whenever they have a title (no data tables needed).
        $page->section(SectionKey::Promise)->update(['title' => ['en' => 'Promise section heading'], 'sort_order' => 20]);
        $page->section(SectionKey::Cta)->update(['title' => ['en' => 'CTA section heading'], 'sort_order' => 5]);
        $page->section(SectionKey::Blog)->update(['title' => ['en' => 'Blog section heading'], 'is_visible' => false]);

        $response = $this->get('/pricing/'.$page->slug);

        $response->assertOk();
        $response->assertSeeInOrder(['CTA section heading', 'Promise section heading']);
        $response->assertDontSee('Blog section heading');
    }

    public function test_breadcrumb_links_to_the_treatment_category(): void
    {
        $category = ServiceCategory::factory()->create(['name' => ['en' => 'Hair Transplant Surgery']]);
        $page = CostPage::factory()->inCategory($category)->create();

        $this->get('/pricing/'.$page->slug)
            ->assertOk()
            ->assertSee('Hair Transplant Surgery')
            ->assertSee($category->serviceUrl(), false);
    }

    public function test_hreflang_alternates_only_list_translated_locales(): void
    {
        $page = CostPage::factory()->create(['title' => ['en' => 'Cost', 'fr' => 'Coût']]);

        $response = $this->get('/pricing/'.$page->slug);

        $response->assertSee('hreflang="fr"', false);
        $response->assertDontSee('hreflang="es"', false);
    }

    public function test_seeder_creates_the_page_once_and_never_overwrites_edits(): void
    {
        $this->seed(ServiceCategorySeeder::class);

        $this->seed(CostPageSeeder::class);

        $page = CostPage::query()->where('slug', CostPageSeeder::SLUG)->firstOrFail();
        $this->assertNotNull($page->service_category_id);
        $this->assertCount(count(SectionKey::cases()), $page->sections);
        $this->assertSame('Recovery', $page->section(SectionKey::Timeline)->translate('eyebrow', 'en'));

        $page->section(SectionKey::Timeline)->update(['eyebrow' => ['en' => 'Edited by admin']]);

        $this->seed(CostPageSeeder::class);

        $this->assertSame(1, CostPage::query()->count());
        $this->assertSame('Edited by admin', $page->fresh()->section(SectionKey::Timeline)->translate('eyebrow', 'en'));
    }
}
