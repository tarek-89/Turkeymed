<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Post;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_author_page_lists_their_published_content_with_person_schema(): void
    {
        $author = Author::factory()->create([
            'name' => 'Dr. Ayse Yilmaz',
            'slug' => 'dr-ayse-yilmaz',
            'credentials' => 'MD',
            'bio' => ['en' => 'Hair transplant specialist.'],
        ]);
        $post = Post::factory()->create(['author_id' => $author->id, 'title' => 'The DHI Guide']);
        $otherPost = Post::factory()->create(['title' => 'Unrelated Article']);

        $response = $this->get('/authors/dr-ayse-yilmaz');

        $response->assertOk();
        $response->assertSee('Dr. Ayse Yilmaz', false);
        $response->assertSee('MD', false);
        $response->assertSee('The DHI Guide', false);
        $response->assertDontSee('Unrelated Article', false);
        $response->assertSee('"@type":"Person"', false);
    }

    public function test_unpublished_author_returns_404(): void
    {
        Author::factory()->unpublished()->create(['slug' => 'hidden-author']);

        $this->get('/authors/hidden-author')->assertNotFound();
    }

    public function test_unknown_author_returns_404(): void
    {
        $this->get('/authors/nobody')->assertNotFound();
    }

    public function test_post_page_shows_byline_and_reviewer_schema(): void
    {
        $author = Author::factory()->create(['name' => 'Dr. Mehmet Demir', 'credentials' => 'MD']);
        $reviewer = Author::factory()->create(['name' => 'Dr. Selin Kaya']);
        $post = Post::factory()->create([
            'author_id' => $author->id,
            'reviewer_id' => $reviewer->id,
            'last_reviewed_at' => now(),
        ]);

        $response = $this->get($post->url());

        $response->assertOk();
        $response->assertSee('Dr. Mehmet Demir', false);
        $response->assertSee(__('content.reviewed_by'), false);
        $response->assertSee('"reviewedBy"', false);
        $response->assertSee('"@type":"BlogPosting"', false);
    }

    public function test_service_page_emits_medical_web_page_schema(): void
    {
        $service = Service::factory()->create();

        $response = $this->get($service->url());

        $response->assertOk();
        $response->assertSee('"@type":"MedicalWebPage"', false);
        $response->assertSee('"@type":"MedicalProcedure"', false);
    }
}
