<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_users_page_lists_users(): void
    {
        $this->actingAs(User::factory()->create());
        User::factory()->create(['email' => 'second-admin@turkeymed.net']);

        $this->get(UserResource::getUrl('index'))
            ->assertOk()
            ->assertSee('second-admin@turkeymed.net');
    }

    public function test_user_passwords_are_hashed_by_the_model_cast(): void
    {
        $user = User::create([
            'name' => 'New Admin',
            'email' => 'new-admin@turkeymed.net',
            'password' => 'plain-secret',
        ]);

        $this->assertNotSame('plain-secret', $user->password);
        $this->assertTrue(Hash::check('plain-secret', $user->password));
    }
}
