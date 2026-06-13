<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_post_page_renders_with_reading_time(): void
    {
        $post = Post::factory()->create();

        $response = $this->get('/'.$post->slug);

        $response->assertOk();
        $response->assertSee($post->title);
        $response->assertSee(__('posts.min_read', ['minutes' => $post->readingTime()]));
    }

    public function test_breadcrumb_includes_the_category_when_assigned(): void
    {
        $category = ServiceCategory::factory()->create(['name' => 'Hair Transplant Surgery']);
        $post = Post::factory()->inCategory($category)->create();

        $response = $this->get('/'.$post->slug);

        $response->assertOk();
        $response->assertSee('Hair Transplant Surgery');
        $response->assertSee($category->url(), false);
    }

    public function test_related_posts_come_only_from_the_same_category(): void
    {
        $category = ServiceCategory::factory()->create();
        $post = Post::factory()->inCategory($category)->create();
        $siblings = Post::factory()->count(3)->inCategory($category)->create();
        Post::factory()->count(3)->create(); // uncategorized noise

        $related = $post->relatedPosts();

        $this->assertCount(3, $related);
        $this->assertEqualsCanonicalizing(
            $siblings->pluck('id')->all(),
            $related->pluck('id')->all(),
        );
    }

    public function test_related_posts_are_empty_without_a_category(): void
    {
        $post = Post::factory()->create();
        Post::factory()->count(3)->create();

        $this->assertCount(0, $post->relatedPosts());
    }

    public function test_related_posts_exclude_drafts(): void
    {
        $category = ServiceCategory::factory()->create();
        $post = Post::factory()->inCategory($category)->create();
        $draft = Post::factory()->inCategory($category)->draft()->create();

        $this->assertFalse($post->relatedPosts()->pluck('id')->contains($draft->id));
    }

    public function test_reading_time_is_at_least_one_minute(): void
    {
        $post = Post::factory()->create(['body' => '<p>Short.</p>']);

        $this->assertSame(1, $post->readingTime());
    }
}
