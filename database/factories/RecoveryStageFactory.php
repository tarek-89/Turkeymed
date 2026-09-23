<?php

namespace Database\Factories;

use App\Models\CostPage;
use App\Models\RecoveryStage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecoveryStage>
 */
class RecoveryStageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $month = fake()->numberBetween(1, 12);

        return [
            'cost_page_id' => CostPage::factory(),
            'month' => $month,
            'percent' => min(100, $month * 8),
            'when_label' => ['en' => 'After '.$month.' months'],
            'short_label' => ['en' => $month.'m'],
            'title' => ['en' => ucfirst(fake()->unique()->words(2, true))],
            'body' => ['en' => fake()->sentence(10)],
            'tip' => ['en' => fake()->sentence(6)],
            'sort_order' => $month,
        ];
    }
}
