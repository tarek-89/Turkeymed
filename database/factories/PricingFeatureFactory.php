<?php

namespace Database\Factories;

use App\Models\CostPage;
use App\Models\PricingFeature;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PricingFeature>
 */
class PricingFeatureFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cost_page_id' => CostPage::factory(),
            'label' => ['en' => ucfirst(fake()->unique()->words(3, true))],
            'is_published' => true,
            'sort_order' => fake()->numberBetween(1, 20),
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (): array => ['is_published' => false]);
    }
}
