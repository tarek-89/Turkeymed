<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServicePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_service_page_renders_successfully(): void
    {
        $service = Service::factory()->create();

        $response = $this->get('/'.$service->slug);

        $response->assertOk();
        $response->assertSee($service->title);
    }

    public function test_breadcrumb_includes_the_category_when_assigned(): void
    {
        $category = ServiceCategory::factory()->create(['name' => 'Hair Transplant Surgery']);
        $service = Service::factory()->inCategory($category)->create();

        $response = $this->get('/'.$service->slug);

        $response->assertOk();
        $response->assertSee('Hair Transplant Surgery');
        $response->assertSee($category->url(), false);
    }

    public function test_breadcrumb_falls_back_to_home_and_title_without_a_category(): void
    {
        $service = Service::factory()->create(['title' => 'Beard Transplant']);

        $response = $this->get('/'.$service->slug);

        $response->assertOk();
        $response->assertSee('Beard Transplant');
    }

    public function test_related_services_from_the_same_category_are_listed(): void
    {
        $category = ServiceCategory::factory()->create();
        $service = Service::factory()->inCategory($category)->create();
        $related = Service::factory()->count(3)->inCategory($category)->create();

        $response = $this->get('/'.$service->slug);

        $response->assertOk();
        foreach ($related as $item) {
            $response->assertSee($item->title);
            $response->assertSee($item->url(), false);
        }
    }

    public function test_related_services_exclude_drafts_and_other_categories(): void
    {
        $category = ServiceCategory::factory()->create();
        $otherCategory = ServiceCategory::factory()->create();
        $service = Service::factory()->inCategory($category)->create();

        $draft = Service::factory()->inCategory($category)->draft()->create();
        $unrelated = Service::factory()->inCategory($otherCategory)->create();

        $response = $this->get('/'.$service->slug);

        $response->assertOk();
        $response->assertDontSee($draft->title);
        $response->assertDontSee($unrelated->title);
    }

    public function test_related_services_are_capped_at_six(): void
    {
        $category = ServiceCategory::factory()->create();
        $service = Service::factory()->inCategory($category)->create();
        Service::factory()->count(10)->inCategory($category)->create();

        $this->assertCount(6, $service->relatedServices());
    }
}
