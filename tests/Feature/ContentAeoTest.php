<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentAeoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_summary_is_preferred_for_the_meta_description(): void
    {
        $post = Post::factory()->create([
            'summary' => 'DHI is a minimally invasive hair transplant method.',
            'excerpt' => 'A different marketing excerpt.',
            'meta_description' => null,
        ]);

        $this->assertStringContainsString('DHI is a minimally invasive', $post->metaDescription());
    }

    public function test_post_renders_faq_accordion_and_schema(): void
    {
        $post = Post::factory()->create([
            'faqs' => [
                ['question' => 'Is the procedure safe?', 'answer' => 'Yes, it is very safe.'],
            ],
        ]);

        $response = $this->get($post->url());

        $response->assertOk();
        $response->assertSee('Is the procedure safe?', false);
        $response->assertSee('"@type":"FAQPage"', false);
        $response->assertSee('"@type":"Question"', false);
    }

    public function test_service_cross_links_to_articles_in_the_same_category(): void
    {
        $category = ServiceCategory::factory()->create();
        $service = Service::factory()->inCategory($category)->create();
        Post::factory()->inCategory($category)->create(['title' => 'Recovery Tips Article']);

        $this->get($service->url())
            ->assertOk()
            ->assertSee('Recovery Tips Article', false);
    }

    public function test_post_cross_links_to_treatments_in_the_same_category(): void
    {
        $category = ServiceCategory::factory()->create();
        $post = Post::factory()->inCategory($category)->create();
        Service::factory()->inCategory($category)->create(['title' => 'DHI Technique Service']);

        $this->get($post->url())
            ->assertOk()
            ->assertSee('DHI Technique Service', false);
    }

    public function test_aggregate_rating_appears_in_organization_schema(): void
    {
        Testimonial::factory()->create(['rating' => 5, 'is_published' => true]);
        Testimonial::factory()->create(['rating' => 4, 'is_published' => true]);

        $response = $this->get('/');

        $response->assertSee('"@type":"AggregateRating"', false);
        $response->assertSee('"reviewCount":2', false);
    }
}
