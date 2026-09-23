<?php

namespace Database\Factories;

use App\Models\CostPage;
use App\Models\GraftZone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GraftZone>
 */
class GraftZoneFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $min = fake()->numberBetween(500, 1200);

        return [
            'cost_page_id' => CostPage::factory(),
            'number' => fake()->unique()->numberBetween(1, 6),
            'name' => ['en' => ucfirst(fake()->word())],
            'min_grafts' => $min,
            'max_grafts' => $min + 200,
            'sort_order' => 0,
        ];
    }
}
