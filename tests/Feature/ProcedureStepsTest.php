<?php

namespace Tests\Feature;

use App\Models\CostPage;
use App\Models\CostProcedureStep;
use App\Support\Cost\SectionKey;
use Database\Seeders\CostPageSeeder;
use Database\Seeders\ServiceCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcedureStepsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_steps_are_numbered_by_order_and_unpublished_ones_hidden(): void
    {
        $page = CostPage::factory()->create();
        CostProcedureStep::factory()->for($page, 'page')->create(['title' => ['en' => 'Extraction'], 'duration' => ['en' => '2–3 h'], 'sort_order' => 2]);
        CostProcedureStep::factory()->for($page, 'page')->create(['title' => ['en' => 'Consultation'], 'sort_order' => 1]);
        CostProcedureStep::factory()->for($page, 'page')->unpublished()->create(['title' => ['en' => 'Hidden step']]);
        $page->section(SectionKey::Procedure)->update([
            'title' => ['en' => 'How is your hair transplant performed?'],
            'content' => ['en' => ['advantages_title' => 'Advantages of FUE', 'link_label' => 'Read the guide', 'link_url' => 'https://example.com/fue']],
        ]);
        $page->section(SectionKey::Procedure)->items()->create(['group' => 'advantages', 'title' => ['en' => 'No linear scar'], 'sort_order' => 1]);

        $response = $this->get('/pricing/'.$page->slug);

        $response->assertOk();
        $response->assertSee('How is your hair transplant performed?');
        $response->assertSeeInOrder(['Consultation', 'Extraction']);
        $response->assertSee('2–3 h');
        $response->assertDontSee('Hidden step');
        $response->assertSee('Advantages of FUE');
        $response->assertSee('No linear scar');
        $response->assertSee('href="https://example.com/fue"', false);
        $response->assertSee('Read the guide');
    }

    public function test_link_is_hidden_without_a_url_and_section_skipped_without_steps(): void
    {
        $page = CostPage::factory()->create();
        CostProcedureStep::factory()->for($page, 'page')->create();
        $page->section(SectionKey::Procedure)->update(['content' => ['en' => ['link_label' => 'Read the guide']]]);

        $this->get('/pricing/'.$page->slug)->assertOk()->assertDontSee('Read the guide');

        $empty = CostPage::factory()->create();
        $empty->section(SectionKey::Procedure)->update(['title' => ['en' => 'How is it performed?']]);

        $this->get('/pricing/'.$empty->slug)->assertOk()->assertDontSee('How is it performed?');
    }

    public function test_seeder_adds_steps_and_advantages_once(): void
    {
        $this->seed(ServiceCategorySeeder::class);
        $this->seed(CostPageSeeder::class);

        $page = CostPage::query()->where('slug', CostPageSeeder::SLUG)->firstOrFail();

        $this->assertCount(4, $page->procedureSteps);
        $this->assertSame('~45 min', $page->procedureSteps->first()->translate('duration'));
        $this->assertCount(5, $page->section(SectionKey::Procedure)->itemsIn('advantages'));

        $page->procedureSteps()->first()->delete();
        $this->seed(CostPageSeeder::class);

        $this->assertCount(3, $page->fresh()->procedureSteps);
    }
}
