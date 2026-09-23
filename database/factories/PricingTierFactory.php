<?php

namespace Database\Factories;

use App\Models\CostPage;
use App\Models\PricingTier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PricingTier>
 */
class PricingTierFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cost_page_id' => CostPage::factory(),
            'name' => ['en' => ucfirst(fake()->unique()->word())],
            'subtitle' => ['en' => fake()->words(3, true)],
            'highlights' => ['en' => ['Max grafts']],
            'is_featured' => false,
            'is_published' => true,
            'sort_order' => fake()->numberBetween(1, 5),
        ];
    }

    public function featured(): static
    {
        return $this->state(fn (): array => ['is_featured' => true]);
    }

    public function unpublished(): static
    {
        return $this->state(fn (): array => ['is_published' => false]);
    }
}
