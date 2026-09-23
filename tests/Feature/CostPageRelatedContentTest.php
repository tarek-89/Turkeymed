<?php

namespace Tests\Feature;

use App\Models\CostPage;
use App\Models\PatientResult;
use App\Models\Post;
use App\Models\ServiceCategory;
use App\Support\Cost\SectionKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CostPageRelatedContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_results_carousel_shows_only_published_consented_results_of_the_category(): void
    {
        $category = ServiceCategory::factory()->create();
        $other = ServiceCategory::factory()->create();
        $page = CostPage::factory()->inCategory($category)->create();
        $page->section(SectionKey::Results)->update(['title' => ['en' => 'Before and after']]);

        PatientResult::factory()->inCategory($category)->create(['grafts_count' => 4800, 'months_to_result' => 8]);
        PatientResult::factory()->inCategory($category)->unconsented()->create(['grafts_count' => 9999]);
        PatientResult::factory()->inCategory($other)->create(['grafts_count' => 7777]);

        $response = $this->get('/pricing/'.$page->slug);

        $response->assertOk();
        $response->assertSee('Before and after');
        $response->assertSee('4,800');
        $response->assertDontSee('9,999');
        $response->assertDontSee('7,777');
    }

    public function test_results_and_blog_sections_hide_when_there_is_nothing_to_show(): void
    {
        $page = CostPage::factory()->create();
        $page->section(SectionKey::Results)->update(['title' => ['en' => 'Before and after']]);
        $page->section(SectionKey::Blog)->update(['title' => ['en' => 'Learn before you leap']]);

        $this->get('/pricing/'.$page->slug)
            ->assertOk()
            ->assertDontSee('Before and after')
            ->assertDontSee('Learn before you leap');
    }

    public function test_blog_section_lists_category_posts_in_the_page_language_with_a_link(): void
    {
        $category = ServiceCategory::factory()->create(['slug' => 'hair-transplant-surgery']);
        $page = CostPage::factory()->inCategory($category)->create();
        $page->section(SectionKey::Blog)->update([
            'title' => ['en' => 'Learn before you leap'],
            'content' => ['en' => ['link_label' => 'All articles', 'posts_count' => '2']],
        ]);

        Post::factory()->inCategory($category)->create(['title' => 'Newest article', 'published_at' => now()->subDay()]);
        Post::factory()->inCategory($category)->create(['title' => 'Older article', 'published_at' => now()->subDays(2)]);
        Post::factory()->inCategory($category)->create(['title' => 'Oldest article', 'published_at' => now()->subDays(3)]);
        Post::factory()->inCategory($category)->draft()->create(['title' => 'Draft article']);
        Post::factory()->inCategory($category)->language('fr')->create(['title' => 'Article français']);

        $response = $this->get('/pricing/'.$page->slug);

        $response->assertOk();
        $response->assertSee('Learn before you leap');
        $response->assertSeeInOrder(['Newest article', 'Older article']);
        $response->assertDontSee('Oldest article');
        $response->assertDontSee('Draft article');
        $response->assertDontSee('Article français');
        $response->assertSee('All articles');
        $response->assertSee($category->blogUrl(), false);
    }

    public function test_cta_banner_uses_the_section_texts(): void
    {
        $page = CostPage::factory()->create();
        $page->section(SectionKey::Cta)->update([
            'title' => ['en' => 'Know your number. Now get your price.'],
            'lead' => ['en' => 'Send your photos.'],
            'content' => ['en' => ['cta_label' => 'Get a free consultation', 'cta_url' => 'https://example.com/quote']],
        ]);

        $this->get('/pricing/'.$page->slug)
            ->assertOk()
            ->assertSee('Know your number. Now get your price.')
            ->assertSee('Send your photos.')
            ->assertSee('href="https://example.com/quote"', false)
            ->assertSee('wa.me/', false);
    }
}
