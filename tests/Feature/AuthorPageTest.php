<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Service;
use App\Models\User;
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

    public function test_post_page_shows_byline_and_author_schema(): void
    {
        $author = User::factory()->author()->create(['name' => 'Dr. Mehmet Demir', 'credentials' => 'MD']);
        $post = Post::factory()->create(['created_by' => $author->id]);

        $response = $this->get($post->url());

        $response->assertOk();
        $response->assertSee('Dr. Mehmet Demir', false);
        $response->assertSee(__('content.written_by'), false);
        $response->assertSee('"@type":"BlogPosting"', false);
        $response->assertSee('"@type":"Person"', false);
    }

    public function test_author_profile_page_is_gone(): void
    {
        $author = User::factory()->author()->create(['slug' => 'mohamad-elhomsi']);

        $this->get('/authors/'.$author->slug)->assertNotFound();
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
