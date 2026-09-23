<?php

namespace Database\Factories;

use App\Models\CostPage;
use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CostPage>
 */
class CostPageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = ucfirst(fake()->unique()->words(4, true)).' cost in Turkey';

        return [
            'slug' => Str::slug($title),
            'is_published' => true,
            'title' => ['en' => $title],
            'meta_description' => ['en' => fake()->sentence(14)],
            'currency' => 'EUR',
            'price_range_min' => 1500,
            'price_range_max' => 3200,
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (): array => ['is_published' => false]);
    }

    public function inCategory(ServiceCategory $category): static
    {
        return $this->state(fn (): array => ['service_category_id' => $category->id]);
    }
}
