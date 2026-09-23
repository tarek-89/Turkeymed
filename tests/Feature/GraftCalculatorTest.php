<?php

namespace Tests\Feature;

use App\Models\CostPage;
use App\Models\GraftPreset;
use App\Support\Cost\SectionKey;
use Database\Seeders\CostPageSeeder;
use Database\Seeders\ServiceCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GraftCalculatorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_a_new_page_gets_the_six_default_zones(): void
    {
        $page = CostPage::factory()->create();

        $this->assertSame([1, 2, 3, 4, 5, 6], $page->zones->pluck('number')->all());
        $this->assertSame('Hairline', $page->zones->first()->translate('name'));
        $this->assertSame(600, $page->zones->first()->min_grafts);
    }

    public function test_estimate_maths_match_the_design(): void
    {
        $page = CostPage::factory()->create(['price_range_min' => 1500, 'two_session_price_from' => 2900]);

        $estimate = $page->calculator()->estimate([1, 2, 3]);

        $this->assertSame(2500, $estimate['min']);
        $this->assertSame(3200, $estimate['max']);
        $this->assertSame(2850, $estimate['mid']);
        $this->assertSame(6250, $estimate['hairs']);
        $this->assertFalse($estimate['two_sessions']);
        $this->assertSame('6–7 h', $estimate['duration']);
        $this->assertSame(1500, $estimate['price_from']);

        $large = $page->calculator()->estimate([1, 2, 3, 4, 5, 6]);

        $this->assertSame(6300, $large['mid']);
        $this->assertTrue($large['two_sessions']);
        $this->assertSame('7–8 h', $large['duration']);
        $this->assertSame(2900, $large['price_from']);

        $empty = $page->calculator()->estimate([]);

        $this->assertSame(0, $empty['mid']);
        $this->assertNull($empty['duration']);
    }

    public function test_duration_rules_from_the_admin_override_the_defaults(): void
    {
        $page = CostPage::factory()->create([
            'duration_rules' => [
                ['max_grafts' => 1000, 'label' => ['en' => 'Short', 'fr' => 'Court']],
                ['max_grafts' => null, 'label' => ['en' => 'Long']],
            ],
        ]);

        $this->assertSame('Short', $page->calculator()->durationFor(900));
        $this->assertSame('Long', $page->calculator()->durationFor(4000));
        $this->assertSame('Court', $page->calculator()->durationRules('fr')[0]['label']);
    }

    public function test_calculator_renders_server_side_with_the_default_selection(): void
    {
        $page = CostPage::factory()->create(['default_zone_numbers' => [1, 2, 3]]);
        GraftPreset::factory()->for($page, 'page')->create(['label' => ['en' => 'NW 3V'], 'zone_numbers' => [1, 2, 3]]);
        GraftPreset::factory()->for($page, 'page')->unpublished()->create(['label' => ['en' => 'Hidden preset']]);

        $response = $this->get('/pricing/'.$page->slug);

        $response->assertOk();
        $response->assertSee('data-graft-calculator', false);
        $response->assertSee('Hairline');
        $response->assertSee('Mid-scalp');
        $response->assertSee('2,850');
        $response->assertSee('NW 3V');
        $response->assertDontSee('Hidden preset');
        $this->assertMatchesRegularExpression('/data-zone="1"\s+aria-pressed="true"/', $response->getContent());
        $this->assertMatchesRegularExpression('/data-zone="4"\s+aria-pressed="false"/', $response->getContent());
        $response->assertSee('"hairsPerGraft":2.2', false);
        $response->assertSee('€1,500');
        $response->assertSee('head-zones.webp');
        $response->assertDontSee('x-service.sticky-cta');
    }

    public function test_prices_follow_the_visitor_language(): void
    {
        $page = CostPage::factory()->create(['title' => ['en' => 'Cost', 'fr' => 'Coût', 'ar' => 'التكلفة']]);

        $this->assertStringContainsString('€', $page->money(1500, 'fr'));
        $this->assertStringNotContainsString('1,500', $page->money(1500, 'fr'));
        $this->assertStringContainsString('1,500', $page->money(1500, 'ar'));
        $this->assertSame('€1,500', $page->money(1500, 'en'));
    }

    public function test_seeder_adds_presets_texts_and_badges_once(): void
    {
        $this->seed(ServiceCategorySeeder::class);
        $this->seed(CostPageSeeder::class);

        $page = CostPage::query()->where('slug', CostPageSeeder::SLUG)->firstOrFail();
        $section = $page->section(SectionKey::Calculator);

        $this->assertCount(6, $page->zones);
        $this->assertCount(5, $page->presets);
        $this->assertSame('Get my free quote', $section->text('cta_label', 'en'));
        $this->assertCount(3, $section->itemsIn('badges'));

        $page->presets()->first()->update(['label' => ['en' => 'Edited']]);
        $this->seed(CostPageSeeder::class);

        $this->assertCount(5, $page->fresh()->presets);
        $this->assertSame('Edited', $page->fresh()->presets->first()->translate('label'));
    }
}
