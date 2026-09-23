<?php

namespace Database\Factories;

use App\Models\CostComparisonRow;
use App\Models\CostPage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CostComparisonRow>
 */
class CostComparisonRowFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cost_page_id' => CostPage::factory(),
            'label' => ['en' => ucfirst(fake()->unique()->words(2, true))],
            'ours' => ['en' => fake()->sentence(4)],
            'theirs' => ['en' => fake()->sentence(4)],
            'is_published' => true,
            'sort_order' => fake()->numberBetween(1, 9),
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (): array => ['is_published' => false]);
    }
}
