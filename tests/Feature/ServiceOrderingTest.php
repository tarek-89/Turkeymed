<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Support\Navigation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceOrderingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_category_page_lists_services_by_sort_order_then_title(): void
    {
        $category = ServiceCategory::factory()->create();

        Service::factory()->inCategory($category)->create(['title' => 'Alpha', 'sort_order' => 3]);
        Service::factory()->inCategory($category)->create(['title' => 'Zulu', 'sort_order' => 1]);
        Service::factory()->inCategory($category)->create(['title' => 'Mike', 'sort_order' => 2]);
        Service::factory()->inCategory($category)->create(['title' => 'Bravo', 'sort_order' => 2]);

        $response = $this->get($category->url());

        $response->assertOk();
        $response->assertSeeInOrder(['Zulu', 'Bravo', 'Mike', 'Alpha']);
    }

    public function test_header_menu_lists_services_by_sort_order(): void
    {
        $category = ServiceCategory::factory()->create();

        Service::factory()->inCategory($category)->create(['title' => 'Alpha', 'sort_order' => 2]);
        Service::factory()->inCategory($category)->create(['title' => 'Zulu', 'sort_order' => 1]);

        $menu = Navigation::treatmentMenu();

        $this->assertSame(['Zulu', 'Alpha'], array_column($menu[0]['services'], 'label'));
    }

    public function test_header_menu_lists_categories_by_sort_order(): void
    {
        $second = ServiceCategory::factory()->create(['name' => ['en' => 'Aardvark'], 'sort_order' => 2]);
        $first = ServiceCategory::factory()->create(['name' => ['en' => 'Zebra'], 'sort_order' => 1]);

        Service::factory()->inCategory($second)->create();
        Service::factory()->inCategory($first)->create();

        $menu = Navigation::treatmentMenu();

        $this->assertSame(['Zebra', 'Aardvark'], array_column($menu, 'label'));
    }

    public function test_changing_sort_order_syncs_to_translations(): void
    {
        $english = Service::factory()->inTranslationGroup(7)->create(['sort_order' => 0]);
        $arabic = Service::factory()->inTranslationGroup(7)->language('ar')->create(['sort_order' => 0]);
        $unrelated = Service::factory()->create(['sort_order' => 0]);

        $english->update(['sort_order' => 5]);

        $this->assertSame(5, $arabic->fresh()->sort_order);
        $this->assertSame(0, $unrelated->fresh()->sort_order);
    }

    public function test_sync_helper_mirrors_sort_order_to_translations(): void
    {
        $english = Service::factory()->inTranslationGroup(9)->create(['sort_order' => 4]);
        $arabic = Service::factory()->inTranslationGroup(9)->language('ar')->create(['sort_order' => 0]);

        $english->syncSortOrderToTranslations();

        $this->assertSame(4, $arabic->fresh()->sort_order);
    }

    public function test_move_up_swaps_with_previous_service_in_same_category_and_language(): void
    {
        $category = ServiceCategory::factory()->create();
        $first = Service::factory()->inCategory($category)->create(['title' => 'First', 'sort_order' => 1]);
        $second = Service::factory()->inCategory($category)->create(['title' => 'Second', 'sort_order' => 2]);
        $third = Service::factory()->inCategory($category)->create(['title' => 'Third', 'sort_order' => 3]);
        $otherLanguage = Service::factory()->inCategory($category)->language('ar')->create(['sort_order' => 1]);

        $third->moveBy(-1);

        $this->assertSame(1, $first->fresh()->sort_order);
        $this->assertSame(2, $third->fresh()->sort_order);
        $this->assertSame(3, $second->fresh()->sort_order);
        $this->assertSame(1, $otherLanguage->fresh()->sort_order);
    }

    public function test_move_down_syncs_new_position_to_translations(): void
    {
        $category = ServiceCategory::factory()->create();
        $first = Service::factory()->inCategory($category)->inTranslationGroup(1)->create(['sort_order' => 1]);
        $firstArabic = Service::factory()->inCategory($category)->inTranslationGroup(1)->language('ar')->create(['sort_order' => 1]);
        $second = Service::factory()->inCategory($category)->inTranslationGroup(2)->create(['sort_order' => 2]);
        $secondArabic = Service::factory()->inCategory($category)->inTranslationGroup(2)->language('ar')->create(['sort_order' => 2]);

        $first->moveBy(1);

        $this->assertSame(2, $first->fresh()->sort_order);
        $this->assertSame(2, $firstArabic->fresh()->sort_order);
        $this->assertSame(1, $second->fresh()->sort_order);
        $this->assertSame(1, $secondArabic->fresh()->sort_order);
    }

    public function test_moving_past_the_edges_is_a_no_op(): void
    {
        $category = ServiceCategory::factory()->create();
        $only = Service::factory()->inCategory($category)->create(['sort_order' => 4]);

        $only->moveBy(-1);
        $only->moveBy(1);

        $this->assertSame(4, $only->fresh()->sort_order);
    }

    public function test_category_move_up_renumbers_categories(): void
    {
        $a = ServiceCategory::factory()->create(['name' => ['en' => 'A'], 'sort_order' => 1]);
        $b = ServiceCategory::factory()->create(['name' => ['en' => 'B'], 'sort_order' => 2]);
        $c = ServiceCategory::factory()->create(['name' => ['en' => 'C'], 'sort_order' => 3]);

        $c->moveBy(-1);

        $this->assertSame([1, 3, 2], [$a->fresh()->sort_order, $b->fresh()->sort_order, $c->fresh()->sort_order]);
    }

    public function test_saving_without_changing_sort_order_does_not_touch_translations(): void
    {
        $english = Service::factory()->inTranslationGroup(3)->create(['sort_order' => 1]);
        $arabic = Service::factory()->inTranslationGroup(3)->language('ar')->create(['sort_order' => 8]);

        $english->update(['title' => 'Renamed']);

        $this->assertSame(8, $arabic->fresh()->sort_order);
    }
}
