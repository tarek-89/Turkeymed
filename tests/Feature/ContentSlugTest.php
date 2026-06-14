<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Service;
use App\Support\ContentSlug;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentSlugTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_slug_used_by_a_service_is_reported_when_editing_a_post(): void
    {
        Service::factory()->create(['slug' => 'hair-transplant', 'language' => 'en']);

        $this->assertTrue(ContentSlug::takenByOtherType('post', 'en', 'hair-transplant'));
    }

    public function test_a_slug_used_by_a_post_is_reported_when_editing_a_service(): void
    {
        Post::factory()->create(['slug' => 'recovery-guide', 'language' => 'en']);

        $this->assertTrue(ContentSlug::takenByOtherType('service', 'en', 'recovery-guide'));
    }

    public function test_the_same_slug_in_a_different_language_is_allowed(): void
    {
        Service::factory()->create(['slug' => 'hair-transplant', 'language' => 'en']);

        $this->assertFalse(ContentSlug::takenByOtherType('post', 'fr', 'hair-transplant'));
    }

    public function test_an_unused_slug_is_free(): void
    {
        $this->assertFalse(ContentSlug::takenByOtherType('post', 'en', 'totally-unused-slug'));
    }

    public function test_a_post_slug_does_not_collide_with_another_post(): void
    {
        // Same-type uniqueness is handled by the form's ->unique() rule; this
        // helper only guards the cross-type case.
        Post::factory()->create(['slug' => 'guide', 'language' => 'en']);

        $this->assertFalse(ContentSlug::takenByOtherType('post', 'en', 'guide'));
    }
}
