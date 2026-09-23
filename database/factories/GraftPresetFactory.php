<?php

namespace Database\Factories;

use App\Models\CostPage;
use App\Models\GraftPreset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GraftPreset>
 */
class GraftPresetFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cost_page_id' => CostPage::factory(),
            'label' => ['en' => 'NW '.fake()->numberBetween(2, 7)],
            'zone_numbers' => [1, 2, 3],
            'is_published' => true,
            'sort_order' => fake()->numberBetween(1, 5),
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (): array => ['is_published' => false]);
    }
}
