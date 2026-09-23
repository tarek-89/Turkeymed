<?php

namespace Database\Factories;

use App\Models\CostPage;
use App\Models\CostProcedureStep;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CostProcedureStep>
 */
class CostProcedureStepFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cost_page_id' => CostPage::factory(),
            'title' => ['en' => ucfirst(fake()->unique()->words(2, true))],
            'body' => ['en' => fake()->sentence(10)],
            'duration' => ['en' => fake()->numberBetween(1, 3).' h'],
            'is_published' => true,
            'sort_order' => fake()->numberBetween(1, 9),
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (): array => ['is_published' => false]);
    }
}
