<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoutingStructureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_a_blog_post_renders_under_blog_category_slug(): void
    {
        $category = ServiceCategory::factory()->create(['slug' => 'hair-transplant-surgery']);
        $post = Post::factory()->inCategory($category)->create(['slug' => 'why-dhi', 'title' => 'Why DHI Wins']);

        $this->get('/blog/hair-transplant-surgery/why-dhi')
            ->assertOk()
            ->assertSee('Why DHI Wins');
    }

    public function test_a_service_renders_under_services_category_slug(): void
    {
        $category = ServiceCategory::factory()->create(['slug' => 'hair-transplant-surgery']);
        $service = Service::factory()->inCategory($category)->create(['slug' => 'dhi-technique', 'title' => 'DHI Technique']);

        $this->get('/services/hair-transplant-surgery/dhi-technique')
            ->assertOk()
            ->assertSee('DHI Technique');
    }

    public function test_a_wrong_category_segment_canonicalises_with_a_301(): void
    {
        $category = ServiceCategory::factory()->create(['slug' => 'hair-transplant-surgery']);
        $post = Post::factory()->inCategory($category)->create(['slug' => 'why-dhi']);

        $this->get('/blog/some-other-category/why-dhi')
            ->assertStatus(301)
            ->assertRedirect($post->url());
    }

    public function test_an_uncategorized_post_uses_the_uncategorized_segment(): void
    {
        $post = Post::factory()->create(['slug' => 'release-note']);

        $this->assertStringContainsString('/blog/uncategorized/release-note', $post->url());

        $this->get('/blog/uncategorized/release-note')->assertOk();
    }

    public function test_the_blog_category_listing_renders(): void
    {
        $category = ServiceCategory::factory()->create(['name' => 'Hair Transplant Surgery', 'slug' => 'hair-transplant-surgery']);
        $post = Post::factory()->inCategory($category)->create(['title' => 'Recovery Tips']);

        $this->get('/blog/hair-transplant-surgery')
            ->assertOk()
            ->assertSee('Hair Transplant Surgery')
            ->assertSee('Recovery Tips');
    }

    public function test_the_services_index_lists_categories(): void
    {
        $category = ServiceCategory::factory()->create(['name' => 'Hair Transplant Surgery']);
        Service::factory()->inCategory($category)->create();

        $this->get('/services')
            ->assertOk()
            ->assertSee('Hair Transplant Surgery');
    }
}
