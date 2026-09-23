<?php

namespace Tests\Feature;

use App\Models\CostPage;
use App\Models\RecoveryPhase;
use App\Models\RecoveryStage;
use App\Support\Cost\SectionKey;
use Database\Seeders\CostPageSeeder;
use Database\Seeders\ServiceCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecoveryTimelineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_stages_render_in_order_with_chart_config(): void
    {
        $page = CostPage::factory()->create();
        RecoveryStage::factory()->for($page, 'page')->create(['month' => 6, 'percent' => 65, 'when_label' => ['en' => 'After 6 months'], 'short_label' => ['en' => '6m'], 'title' => ['en' => 'Clear improvement'], 'sort_order' => 2]);
        RecoveryStage::factory()->for($page, 'page')->create(['month' => 0.5, 'percent' => 5, 'when_label' => ['en' => 'After 2 weeks'], 'short_label' => ['en' => '2w'], 'title' => ['en' => 'Shedding phase'], 'tip' => ['en' => 'Wash gently.'], 'sort_order' => 1]);
        RecoveryPhase::factory()->for($page, 'page')->create(['from_month' => 0, 'to_month' => 3, 'name' => ['en' => 'Healing & shedding'], 'tone' => 'navy']);
        $page->section(SectionKey::Timeline)->update([
            'title' => ['en' => 'When will I see the results?'],
            'content' => ['en' => ['chart_title' => 'Visible result over 12 months', 'tip_label' => 'Your part:'], 'curve_points' => [['month' => 1.6, 'percent' => 3]]],
        ]);

        $response = $this->get('/pricing/'.$page->slug);

        $response->assertOk();
        $response->assertSee('When will I see the results?');
        $response->assertSee('data-recovery-timeline', false);
        $response->assertSeeInOrder(['Shedding phase', 'Clear improvement']);
        $response->assertSee('~65%');
        $response->assertSee('Your part:');
        $response->assertSee('Wash gently.');
        $response->assertSee('Visible result over 12 months');
        $response->assertSee('"curvePoints":[{"month":1.6,"percent":3}]', false);
        $response->assertSee('"tone":"navy"', false);
        $response->assertSee('about 5% visible result after 2 weeks');
    }

    public function test_section_is_skipped_without_stages(): void
    {
        $page = CostPage::factory()->create();
        $page->section(SectionKey::Timeline)->update(['title' => ['en' => 'When will I see the results?']]);

        $this->get('/pricing/'.$page->slug)->assertOk()->assertDontSee('When will I see the results?');
    }

    public function test_seeder_adds_stages_phases_and_curve_points_once(): void
    {
        $this->seed(ServiceCategorySeeder::class);
        $this->seed(CostPageSeeder::class);

        $page = CostPage::query()->where('slug', CostPageSeeder::SLUG)->firstOrFail();
        $section = $page->section(SectionKey::Timeline);

        $this->assertSame([5, 30, 65, 100], $page->recoveryStages->pluck('percent')->all());
        $this->assertCount(3, $page->recoveryPhases);
        $this->assertCount(2, $section->content['curve_points']);
        $this->assertSame('Your part:', $section->text('tip_label', 'en'));

        $page->recoveryStages()->first()->delete();
        $this->seed(CostPageSeeder::class);

        $this->assertCount(3, $page->fresh()->recoveryStages);
    }
}
