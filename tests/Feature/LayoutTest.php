<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_homepage_renders_the_mainframe_chrome(): void
    {
        $response = $this->get('/');

        $response->assertOk();

        // Document shell
        $response->assertSee('lang="en"', false);
        $response->assertSee('dir="ltr"', false);
        $response->assertSee('<main id="main"', false);

        // Accessibility: skip link
        $response->assertSee(__('common.skip_to_content'));

        // Brand in header + footer
        $response->assertSee(config('site.brand'));

        // Footer: localized rights line with the current year
        $response->assertSee((string) date('Y'));

        // WhatsApp floating button
        $response->assertSee(__('common.whatsapp_aria'));
    }

    public function test_html_lang_and_dir_default_to_english(): void
    {
        $response = $this->get('/');

        $response->assertSee('<html lang="en" dir="ltr">', false);
    }
}
