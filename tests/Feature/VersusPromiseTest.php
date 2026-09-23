<?php

namespace Tests\Feature;

use App\Models\CostComparisonRow;
use App\Models\CostPage;
use App\Support\Cost\SectionKey;
use Database\Seeders\CostPageSeeder;
use Database\Seeders\ServiceCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VersusPromiseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function makePage(): CostPage
    {
        $page = CostPage::factory()->create();

        CostComparisonRow::factory()->for($page, 'page')->create(['label' => ['en' => 'Typical price'], 'ours' => ['en' => 'Package price'], 'theirs' => ['en' => 'Per graft'], 'sort_order' => 2]);
        CostComparisonRow::factory()->for($page, 'page')->create(['label' => ['en' => 'Where it happens'], 'ours' => ['en' => 'Istanbul'], 'theirs' => ['en' => 'Varies'], 'sort_order' => 1]);
        CostComparisonRow::factory()->for($page, 'page')->unpublished()->create(['label' => ['en' => 'Hidden row']]);

        $page->section(SectionKey::Versus)->update([
            'title' => ['en' => 'Turkey vs the United States'],
            'content' => ['en' => ['ours_header' => 'Turkey · TurkeyMed', 'theirs_header' => 'United States', 'footnote' => 'US figures vary.']],
        ]);
        $page->section(SectionKey::Promise)->update([
            'title' => ['en' => 'A number picked before anyone has seen your scalp.'],
            'content' => ['en' => ['ask_quote' => 'What happens if my donor area yields fewer grafts?']],
        ]);
        $page->section(SectionKey::Promise)->items()->create(['group' => 'promise_points', 'title' => ['en' => 'Over-harvest the donor area'], 'body' => ['en' => 'Visible thinning.'], 'sort_order' => 1]);

        return $page;
    }

    public function test_table_renders_published_rows_in_order_with_proper_headers(): void
    {
        $page = $this->makePage();

        $response = $this->get('/pricing/'.$page->slug);

        $response->assertOk();
        $response->assertSee('Turkey vs the United States');
        $response->assertSee('<th scope="col" class="is-ours">Turkey · TurkeyMed</th>', false);
        $response->assertSee('United States');
        $response->assertSeeInOrder(['Where it happens', 'Typical price']);
        $response->assertSee('<th scope="row">Where it happens</th>', false);
        $response->assertDontSee('Hidden row');
        $response->assertSee('US figures vary.');
    }

    public function test_promise_renders_beside_the_table_and_only_once(): void
    {
        $page = $this->makePage();

        $response = $this->get('/pricing/'.$page->slug);

        $response->assertOk();
        $response->assertSee('A number picked before anyone has seen your scalp.');
        $response->assertSee('Over-harvest the donor area');
        $response->assertSee('What happens if my donor area yields fewer grafts?');
        $this->assertSame(1, substr_count($response->getContent(), 'Over-harvest the donor area'));
        $this->assertStringContainsString('lg:grid-cols-[minmax(0,1.25fr)_minmax(0,1fr)]', $response->getContent());
    }

    public function test_promise_stands_alone_when_the_table_is_hidden_and_hides_when_toggled_off(): void
    {
        $page = $this->makePage();
        $page->section(SectionKey::Versus)->update(['is_visible' => false]);

        $this->get('/pricing/'.$page->slug)
            ->assertOk()
            ->assertDontSee('Turkey vs the United States')
            ->assertSee('id="promise"', false)
            ->assertSee('Over-harvest the donor area');

        $page->section(SectionKey::Promise)->update(['is_visible' => false]);
        $page->section(SectionKey::Versus)->update(['is_visible' => true]);

        $this->get('/pricing/'.$page->slug)
            ->assertOk()
            ->assertSee('Turkey vs the United States')
            ->assertDontSee('Over-harvest the donor area');
    }

    public function test_seeder_adds_rows_and_callout_once(): void
    {
        $this->seed(ServiceCategorySeeder::class);
        $this->seed(CostPageSeeder::class);

        $page = CostPage::query()->where('slug', CostPageSeeder::SLUG)->firstOrFail();

        $this->assertCount(6, $page->comparisonRows);
        $this->assertSame('United States', $page->section(SectionKey::Versus)->text('theirs_header', 'en'));
        $this->assertCount(3, $page->section(SectionKey::Promise)->itemsIn('promise_points'));

        $page->comparisonRows()->first()->delete();
        $this->seed(CostPageSeeder::class);

        $this->assertCount(5, $page->fresh()->comparisonRows);
    }
}
