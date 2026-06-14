<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Post;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackfillAuthorsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_authors_from_legacy_strings_and_links_content(): void
    {
        $post = Post::factory()->create(['author' => 'Dr. Legacy', 'author_id' => null]);
        $service = Service::factory()->create(['author' => 'Dr. Legacy', 'author_id' => null]);

        $this->artisan('authors:backfill --apply')->assertSuccessful();

        $this->assertDatabaseHas('authors', ['name' => 'Dr. Legacy']);

        $author = Author::query()->where('name', 'Dr. Legacy')->first();
        $this->assertSame($author->id, $post->fresh()->author_id);
        $this->assertSame($author->id, $service->fresh()->author_id);
    }

    public function test_one_author_is_created_per_distinct_name(): void
    {
        Post::factory()->create(['author' => 'Dr. Shared']);
        Service::factory()->create(['author' => 'Dr. Shared']);

        $this->artisan('authors:backfill --apply')->assertSuccessful();

        $this->assertSame(1, Author::query()->where('name', 'Dr. Shared')->count());
    }

    public function test_a_dry_run_creates_nothing(): void
    {
        Post::factory()->create(['author' => 'Dr. Nope']);

        $this->artisan('authors:backfill')->assertSuccessful();

        $this->assertDatabaseCount('authors', 0);
    }
}
