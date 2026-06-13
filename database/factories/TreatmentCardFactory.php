<?php

namespace Database\Factories;

use App\Models\TreatmentCard;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TreatmentCard>
 */
class TreatmentCardFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'variant' => 'default',
            'icon' => fake()->randomElement(['shield', 'star', 'heart', 'check']),
            'title' => ['en' => ucwords(fake()->unique()->words(2, true))],
            'description' => ['en' => fake()->sentence(6)],
            'badge' => null,
            'footnote' => null,
            'url' => '#',
            'is_published' => true,
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }

    public function feature(): static
    {
        return $this->state(fn (): array => [
            'variant' => 'feature',
            'badge' => ['en' => 'Most popular'],
            'footnote' => ['en' => 'From €1,500'],
        ]);
    }

    public function unpublished(): static
    {
        return $this->state(fn (): array => ['is_published' => false]);
    }
}
