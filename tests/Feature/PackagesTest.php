<?php

namespace Tests\Feature;

use App\Models\CostPage;
use App\Models\PricingFeature;
use App\Models\PricingTechnique;
use App\Models\PricingTier;
use App\Support\Cost\SectionKey;
use Database\Seeders\CostPageSeeder;
use Database\Seeders\ServiceCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    /** A page with FUE/DHI and Basic/Standard tiers, prices and two features. */
    private function makePricedPage(): CostPage
    {
        $page = CostPage::factory()->create(['price_range_min' => null, 'price_range_max' => null]);

        $fue = PricingTechnique::factory()->for($page, 'page')->create(['name' => ['en' => 'FUE'], 'sort_order' => 1]);
        $dhi = PricingTechnique::factory()->for($page, 'page')->create(['name' => ['en' => 'DHI'], 'sort_order' => 2]);

        $basic = PricingTier::factory()->for($page, 'page')->create(['name' => ['en' => 'Basic'], 'sort_order' => 1]);
        $standard = PricingTier::factory()->for($page, 'page')->featured()->create(['name' => ['en' => 'Standard'], 'sort_order' => 2]);

        $basic->prices()->createMany([
            ['pricing_technique_id' => $fue->id, 'price' => 1500],
            ['pricing_technique_id' => $dhi->id, 'price' => 1900],
        ]);
        $standard->prices()->createMany([
            ['pricing_technique_id' => $fue->id, 'price' => 1850],
            ['pricing_technique_id' => $dhi->id, 'price' => 2250],
        ]);

        $hotel = PricingFeature::factory()->for($page, 'page')->create(['label' => ['en' => 'Hotel stay'], 'sort_order' => 1]);
        $hotel->values()->createMany([
            ['pricing_tier_id' => $basic->id, 'is_included' => false],
            ['pricing_tier_id' => $standard->id, 'is_included' => true, 'value' => ['en' => '2 nights']],
        ]);

        $tests = PricingFeature::factory()->for($page, 'page')->create(['label' => ['en' => 'Blood tests'], 'sort_order' => 2]);
        $tests->values()->createMany([
            ['pricing_tier_id' => $basic->id, 'is_included' => true],
            ['pricing_tier_id' => $standard->id, 'is_included' => true],
        ]);

        return $page->fresh();
    }

    public function test_price_range_and_from_price_derive_from_published_packages(): void
    {
        $page = $this->makePricedPage();

        $this->assertSame(['min' => 1500, 'max' => 2250], $page->priceRange());
        $this->assertSame(1500, $page->priceFrom());

        // An explicit page range wins over the package span.
        $page->update(['price_range_min' => 1500, 'price_range_max' => 3200]);
        $this->assertSame(['min' => 1500, 'max' => 3200], $page->fresh()->priceRange());

        // Unpublishing a technique removes its prices from the maths.
        $page->techniques()->where('sort_order', 1)->update(['is_published' => false]);
        $this->assertSame(1900, $page->fresh()->priceFrom());
    }

    public function test_features_know_when_they_are_the_same_for_every_tier(): void
    {
        $page = $this->makePricedPage();
        $tiers = $page->tiers;

        $this->assertFalse($page->features->firstWhere('sort_order', 1)->isSameForAll($tiers));
        $this->assertTrue($page->features->firstWhere('sort_order', 2)->isSameForAll($tiers));
    }

    public function test_packages_render_every_price_server_side(): void
    {
        $page = $this->makePricedPage();
        $page->section(SectionKey::Packages)->update(['title' => ['en' => 'Choose your package']]);

        $response = $this->get('/pricing/'.$page->slug);

        $response->assertOk();
        $response->assertSee('Choose your package');
        $response->assertSee('data-packages', false);
        $response->assertSeeInOrder(['Basic', 'Standard']);
        $response->assertSee('€1,500');
        $response->assertSee('€1,900');
        $response->assertSee('€2,250');
        $response->assertSee('Most popular');
        $response->assertSee('2 nights');
        $response->assertSee('data-same', false);
        $response->assertSee('role="switch"', false);
    }

    public function test_unpublished_tiers_and_techniques_are_hidden_and_empty_pages_skip_the_section(): void
    {
        $page = $this->makePricedPage();
        $page->tiers()->where('sort_order', 2)->update(['is_published' => false]);
        $page->techniques()->where('sort_order', 2)->update(['is_published' => false]);

        $this->get('/pricing/'.$page->slug)
            ->assertOk()
            ->assertSee('Basic')
            ->assertDontSee('Standard')
            ->assertDontSee('€1,900');

        $empty = CostPage::factory()->create();
        $empty->section(SectionKey::Packages)->update(['title' => ['en' => 'Choose your package']]);

        $this->get('/pricing/'.$empty->slug)->assertOk()->assertDontSee('Choose your package');
    }

    public function test_seeder_builds_the_full_design_matrix_once(): void
    {
        $this->seed(ServiceCategorySeeder::class);
        $this->seed(CostPageSeeder::class);

        $page = CostPage::query()->where('slug', CostPageSeeder::SLUG)->firstOrFail();

        $this->assertSame(['FUE', 'DHI', 'VIP'], $page->techniques->map->translate('name')->all());
        $this->assertSame(['Basic', 'Standard', 'Premium'], $page->tiers->map->translate('name')->all());
        $this->assertCount(12, $page->features);
        $this->assertSame(9, $page->tiers->sum(fn (PricingTier $tier): int => $tier->prices->count()));
        $this->assertSame(36, $page->features->sum(fn (PricingFeature $feature): int => $feature->values->count()));
        $this->assertSame(3500, $page->tiers->last()->priceFor($page->techniques->last()));
        $this->assertTrue($page->tiers[1]->is_featured);
        $this->assertSame(1500, $page->priceFrom());
        $this->assertCount(4, $page->section(SectionKey::Packages)->itemsIn('payment_methods'));

        $page->tiers()->first()->delete();
        $this->seed(CostPageSeeder::class);

        $this->assertCount(2, $page->fresh()->tiers);
    }
}
