<?php

namespace Tests\Feature;

use App\Filament\Resources\Redirects\RedirectResource;
use App\Models\Redirect;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedirectResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_redirects_admin_page_lists_redirects(): void
    {
        $this->actingAs(User::factory()->create());

        Redirect::factory()->create([
            'from_path' => 'old-marketing-page',
            'to_path' => '/new-page',
        ]);

        $this->get(RedirectResource::getUrl('index'))
            ->assertOk()
            ->assertSee('old-marketing-page');
    }

    public function test_the_admin_page_requires_authentication(): void
    {
        $this->get(RedirectResource::getUrl('index'))->assertRedirect();
    }
}
