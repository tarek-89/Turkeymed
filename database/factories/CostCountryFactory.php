<?php

namespace Database\Factories;

use App\Models\CostCountry;
use App\Models\CostPage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CostCountry>
 */
class CostCountryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $min = fake()->numberBetween(5000, 9000);

        return [
            'cost_page_id' => CostPage::factory(),
            'country_code' => strtoupper(fake()->unique()->lexify('??')),
            'name' => ['en' => fake()->country()],
            'min_price' => $min,
            'max_price' => $min + 4000,
            'note' => ['en' => 'Surgery only'],
            'is_default' => false,
            'is_published' => true,
            'sort_order' => fake()->numberBetween(1, 9),
        ];
    }

    public function default(): static
    {
        return $this->state(fn (): array => ['is_default' => true]);
    }

    public function unpublished(): static
    {
        return $this->state(fn (): array => ['is_published' => false]);
    }
}
