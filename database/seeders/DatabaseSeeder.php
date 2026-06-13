<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User'],
        );

        $this->call(ServiceCategorySeeder::class);
        $this->call(NavCategorySeeder::class);
        $this->call(AboutContentSeeder::class);
        $this->call(ContactContentSeeder::class);
        $this->call(SocialLinkSeeder::class);
        $this->call(HomeContentSeeder::class);
    }
}
