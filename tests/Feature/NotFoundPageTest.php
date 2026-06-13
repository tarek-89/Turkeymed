<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotFoundPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_unknown_url_renders_the_branded_404_page(): void
    {
        $response = $this->get('/this-page-does-not-exist');

        $response->assertNotFound();
        $response->assertSee(__('errors.heading'));
        $response->assertSee(__('errors.back_home'));
        $response->assertSee('<meta name="robots" content="noindex">', false);
    }

    public function test_popular_chips_show_only_categorized_services(): void
    {
        $category = ServiceCategory::factory()->create();
        $categorized = Service::factory()->inCategory($category)->create();
        $uncategorized = Service::factory()->create();

        $response = $this->get('/this-page-does-not-exist');

        $response->assertNotFound();
        $response->assertSee($categorized->title);
        $response->assertDontSee($uncategorized->title);
    }
}
