<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_category_page_lists_published_services_with_links(): void
    {
        $category = ServiceCategory::factory()->create(['name' => 'Hair Transplant Surgery']);
        $services = Service::factory()->count(2)->inCategory($category)->create();

        $response = $this->get('/category/'.$category->slug);

        $response->assertOk();
        $response->assertSee('Hair Transplant Surgery');
        foreach ($services as $service) {
            $response->assertSee($service->title);
            $response->assertSee($service->url(), false);
        }
    }

    public function test_category_page_hides_drafts(): void
    {
        $category = ServiceCategory::factory()->create();
        $draft = Service::factory()->inCategory($category)->draft()->create();

        $response = $this->get('/category/'.$category->slug);

        $response->assertOk();
        $response->assertDontSee($draft->title);
    }

    public function test_category_page_shows_empty_state_when_no_services(): void
    {
        $category = ServiceCategory::factory()->create();

        $response = $this->get('/category/'.$category->slug);

        $response->assertOk();
        $response->assertSee(__('services.category_empty'));
    }

    public function test_category_page_lists_posts_from_the_same_category(): void
    {
        $category = ServiceCategory::factory()->create();
        Service::factory()->inCategory($category)->create();
        $post = Post::factory()->inCategory($category)->create();
        $otherPost = Post::factory()->create();

        $response = $this->get('/category/'.$category->slug);

        $response->assertOk();
        $response->assertSee(__('posts.from_the_blog'));
        $response->assertSee($post->title);
        $response->assertSee($post->url(), false);
        $response->assertDontSee($otherPost->title);
    }

    public function test_blog_section_is_hidden_when_category_has_no_posts(): void
    {
        $category = ServiceCategory::factory()->create();
        Service::factory()->inCategory($category)->create();

        $response = $this->get('/category/'.$category->slug);

        $response->assertOk();
        $response->assertDontSee(__('posts.from_the_blog'));
    }

    public function test_unknown_category_returns_404(): void
    {
        $response = $this->get('/category/does-not-exist');

        $response->assertNotFound();
    }
}
