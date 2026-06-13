<?php

namespace Database\Factories;

use App\Models\Promise;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Promise>
 */
class PromiseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'icon' => fake()->randomElement(Promise::ICONS),
            'title' => ['en' => ucfirst(fake()->unique()->words(2, true))],
            'text' => ['en' => fake()->sentence(10)],
            'is_published' => true,
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (): array => ['is_published' => false]);
    }
}
