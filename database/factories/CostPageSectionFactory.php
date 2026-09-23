<?php

namespace Database\Factories;

use App\Models\CostPage;
use App\Models\CostPageSection;
use App\Support\Cost\SectionKey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CostPageSection>
 */
class CostPageSectionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cost_page_id' => CostPage::factory(),
            'key' => fake()->randomElement(SectionKey::values()),
            'is_visible' => true,
            'sort_order' => fake()->numberBetween(1, 10),
            'title' => ['en' => ucfirst(fake()->words(3, true))],
        ];
    }

    public function hidden(): static
    {
        return $this->state(fn (): array => ['is_visible' => false]);
    }
}
