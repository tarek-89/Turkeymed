<?php

namespace Tests\Feature;

use App\Models\Redirect;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class RedirectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    /**
     * Dispatch a raw request through the HTTP kernel, preserving the exact URI.
     *
     * The usual $this->get() helper runs prepareUrlForRequest(), which strips
     * trailing slashes before the request exists — so it cannot exercise the
     * trailing-slash redirect. Building the Request directly keeps the slash.
     */
    private function getRaw(string $uri): TestResponse
    {
        $response = $this->app->make(Kernel::class)->handle(Request::create($uri, 'GET'));

        return TestResponse::fromBaseResponse($response);
    }

    public function test_an_explicit_redirect_sends_a_permanent_301(): void
    {
        Redirect::factory()->create([
            'from_path' => 'old-page',
            'to_path' => '/new-page',
            'status_code' => 301,
        ]);

        $this->get('/old-page')
            ->assertStatus(301)
            ->assertRedirect('/new-page');
    }

    public function test_an_explicit_redirect_matches_regardless_of_trailing_slash(): void
    {
        Redirect::factory()->create([
            'from_path' => 'category/hair-transplant',
            'to_path' => '/category/hair-transplant-surgery',
        ]);

        $this->get('/category/hair-transplant/')
            ->assertRedirect('/category/hair-transplant-surgery');
    }

    public function test_an_inactive_redirect_is_ignored(): void
    {
        Redirect::factory()->inactive()->create([
            'from_path' => 'old-page',
            'to_path' => '/new-page',
        ]);

        $this->get('/old-page')->assertNotFound();
    }

    public function test_following_a_redirect_records_a_hit(): void
    {
        $redirect = Redirect::factory()->create([
            'from_path' => 'old-page',
            'to_path' => '/new-page',
            'hits' => 0,
        ]);

        $this->get('/old-page');

        $redirect->refresh();
        $this->assertSame(1, $redirect->hits);
        $this->assertNotNull($redirect->last_hit_at);
    }

    public function test_a_trailing_slash_is_redirected_to_the_canonical_url(): void
    {
        $this->getRaw('/some-page/')
            ->assertStatus(301)
            ->assertRedirect('/some-page');
    }

    public function test_a_trailing_slash_redirect_preserves_the_query_string(): void
    {
        $this->getRaw('/some-page/?utm_source=newsletter')
            ->assertRedirect('/some-page?utm_source=newsletter');
    }

    public function test_framework_and_admin_paths_are_never_redirected(): void
    {
        // A stray row must not hijack asset/admin paths.
        Redirect::factory()->create([
            'from_path' => 'build/app.css',
            'to_path' => '/somewhere',
        ]);

        $this->get('/build/app.css')->assertNotFound();
    }
}
