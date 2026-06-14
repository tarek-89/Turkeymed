<?php

namespace Database\Factories;

use App\Models\Redirect;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Redirect>
 */
class RedirectFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'from_path' => trim(fake()->unique()->slug(3), '/'),
            'to_path' => '/'.Str::slug(fake()->words(2, true)),
            'status_code' => 301,
            'is_active' => true,
            'source' => 'wp-import',
            'notes' => null,
            'hits' => 0,
            'last_hit_at' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }

    public function temporary(): static
    {
        return $this->state(fn (): array => ['status_code' => 302]);
    }
}
