<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use App\Support\Url;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_security_headers_are_present_on_html_responses(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertStringContainsString("frame-ancestors 'self'", (string) $response->headers->get('Content-Security-Policy'));
    }

    public function test_jsonld_escapes_script_breakout_in_titles(): void
    {
        $post = Post::factory()->create([
            'title' => 'Hair </script><script>alert(1)</script>',
        ]);

        $response = $this->get($post->url());

        $response->assertOk();
        // The raw breakout sequence must not appear; < and > are hex-escaped in JSON-LD.
        $response->assertDontSee('</script><script>alert(1)', false);
        $response->assertSee('<', false);
    }

    public function test_url_helper_blocks_dangerous_schemes(): void
    {
        $this->assertSame('#', Url::safe('javascript:alert(1)'));
        $this->assertSame('#', Url::safe('data:text/html,<script>'));
        $this->assertSame('#', Url::safe('vbscript:msgbox(1)'));

        $this->assertSame('/category/hair', Url::safe('/category/hair'));
        $this->assertSame('https://x.com', Url::safe('https://x.com'));
        $this->assertSame('#', Url::safe(''));
    }

    public function test_panel_access_respects_email_allow_list(): void
    {
        config(['site.admin_emails' => 'boss@turkeymed.net']);
        $panel = filament()->getPanel('admin');

        $allowed = User::factory()->create(['email' => 'boss@turkeymed.net']);
        $denied = User::factory()->create(['email' => 'random@example.com']);

        $this->assertTrue($allowed->canAccessPanel($panel));
        $this->assertFalse($denied->canAccessPanel($panel));
    }

    public function test_panel_access_open_when_no_allow_list(): void
    {
        config(['site.admin_emails' => '']);
        $panel = filament()->getPanel('admin');

        $user = User::factory()->create();

        $this->assertTrue($user->canAccessPanel($panel));
    }
}
