<?php

namespace Database\Factories;

use App\Models\CostPage;
use App\Models\RecoveryPhase;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecoveryPhase>
 */
class RecoveryPhaseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $from = fake()->numberBetween(0, 6);

        return [
            'cost_page_id' => CostPage::factory(),
            'from_month' => $from,
            'to_month' => $from + 3,
            'name' => ['en' => ucfirst(fake()->unique()->words(2, true))],
            'short_name' => ['en' => ucfirst(fake()->word())],
            'tone' => 'navy',
            'sort_order' => $from,
        ];
    }
}
