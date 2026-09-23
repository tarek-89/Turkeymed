<?php

namespace Database\Factories;

use App\Models\CostPageItem;
use App\Models\CostPageSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CostPageItem>
 */
class CostPageItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cost_page_section_id' => CostPageSection::factory(),
            'group' => 'badges',
            'title' => ['en' => ucfirst(fake()->words(3, true))],
            'is_published' => true,
            'sort_order' => fake()->numberBetween(1, 10),
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (): array => ['is_published' => false]);
    }
}
