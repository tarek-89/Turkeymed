<?php

namespace Database\Factories;

use App\Models\CostPage;
use App\Models\PricingTechnique;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PricingTechnique>
 */
class PricingTechniqueFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cost_page_id' => CostPage::factory(),
            'name' => ['en' => strtoupper(fake()->unique()->lexify('???'))],
            'tagline' => ['en' => fake()->words(2, true)],
            'is_published' => true,
            'sort_order' => fake()->numberBetween(1, 5),
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (): array => ['is_published' => false]);
    }
}
