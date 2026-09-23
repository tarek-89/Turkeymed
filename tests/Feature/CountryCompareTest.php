<?php

namespace Tests\Feature;

use App\Models\CostCountry;
use App\Models\CostPage;
use App\Support\Cost\SectionKey;
use Database\Seeders\CostPageSeeder;
use Database\Seeders\ServiceCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CountryCompareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_savings_are_calculated_from_range_midpoints(): void
    {
        $page = CostPage::factory()->create(['price_range_min' => 1500, 'price_range_max' => 3200]);
        $uk = CostCountry::factory()->for($page, 'page')->create(['min_price' => 8000, 'max_price' => 12000]);

        $savings = $page->savingsFor($uk);

        // Turkey midpoint 2,350 vs UK midpoint 10,000
        $this->assertSame(77, $savings['percent']);
        $this->assertSame(7650, $savings['amount']);
        $this->assertSame(23.5, $savings['bar']);
    }

    public function test_only_one_country_can_be_the_default(): void
    {
        $page = CostPage::factory()->create();
        $first = CostCountry::factory()->for($page, 'page')->default()->create();
        $second = CostCountry::factory()->for($page, 'page')->default()->create();

        $this->assertFalse($first->fresh()->is_default);
        $this->assertTrue($second->fresh()->is_default);
    }

    public function test_comparison_renders_the_default_country_server_side(): void
    {
        $page = CostPage::factory()->create(['price_range_min' => 1500, 'price_range_max' => 3200]);
        CostCountry::factory()->for($page, 'page')->create(['country_code' => 'DE', 'name' => ['en' => 'Germany'], 'min_price' => 6000, 'max_price' => 10000, 'sort_order' => 1]);
        CostCountry::factory()->for($page, 'page')->default()->create(['country_code' => 'GB', 'name' => ['en' => 'United Kingdom'], 'name_in_sentence' => ['en' => 'the United Kingdom'], 'min_price' => 8000, 'max_price' => 12000, 'sort_order' => 2]);
        CostCountry::factory()->for($page, 'page')->unpublished()->create(['name' => ['en' => 'Hidden Country']]);

        $page->section(SectionKey::Compare)->update([
            'title' => ['en' => 'Prices compared worldwide'],
            'content' => ['en' => ['save_text' => 'compared with {country} — about {amount}']],
        ]);

        $response = $this->get('/pricing/'.$page->slug);

        $response->assertOk();
        $response->assertSee('Prices compared worldwide');
        $response->assertSee('data-country-compare', false);
        $response->assertSee('Germany');
        $response->assertDontSee('Hidden Country');
        $response->assertSee('€8,000 – €12,000');
        $response->assertSee('<span data-cmp-percent>77</span>', false);
        $response->assertSee('<b>the United Kingdom</b>', false);
        $response->assertSee('<b>€7,650</b>', false);
        $response->assertSee('width: 23.5%', false);
    }

    public function test_section_is_skipped_without_countries_or_a_price_range(): void
    {
        $page = CostPage::factory()->create(['price_range_min' => null, 'price_range_max' => null]);
        CostCountry::factory()->for($page, 'page')->default()->create();
        $page->section(SectionKey::Compare)->update(['title' => ['en' => 'Prices compared worldwide']]);

        $this->get('/pricing/'.$page->slug)->assertOk()->assertDontSee('Prices compared worldwide');

        $empty = CostPage::factory()->create();
        $empty->section(SectionKey::Compare)->update(['title' => ['en' => 'Prices compared worldwide']]);

        $this->get('/pricing/'.$empty->slug)->assertOk()->assertDontSee('Prices compared worldwide');
    }

    public function test_seeder_adds_three_countries_and_texts_once(): void
    {
        $this->seed(ServiceCategorySeeder::class);
        $this->seed(CostPageSeeder::class);

        $page = CostPage::query()->where('slug', CostPageSeeder::SLUG)->firstOrFail();

        $this->assertSame(['GB', 'US', 'DE'], $page->countries->pluck('country_code')->all());
        $this->assertTrue($page->countries->first()->is_default);
        $this->assertCount(3, $page->section(SectionKey::Compare)->itemsIn('save_points'));

        $page->countries()->where('country_code', 'US')->delete();
        $this->seed(CostPageSeeder::class);

        $this->assertCount(2, $page->fresh()->countries);
    }
}
