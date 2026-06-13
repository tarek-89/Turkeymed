<?php

namespace Database\Factories;

use App\Models\PatientResult;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PatientResult>
 */
class PatientResultFactory extends Factory
{
    /**
     * Default state: a published, consented result.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_category_id' => ServiceCategory::factory(),
            'service_id' => null,
            'before_image' => 'results/'.fake()->numberBetween(2024, 2026).'/'.fake()->unique()->slug(2).'-before.jpg',
            'after_image' => 'results/'.fake()->numberBetween(2024, 2026).'/'.fake()->unique()->slug(2).'-after.jpg',
            'grafts_count' => fake()->numberBetween(1500, 5500),
            'months_to_result' => fake()->randomElement([6, 9, 12]),
            'before_label' => null,
            'after_label' => null,
            'consent_confirmed' => true,
            'is_published' => true,
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (): array => ['is_published' => false]);
    }

    public function unconsented(): static
    {
        return $this->state(fn (): array => [
            'consent_confirmed' => false,
            'is_published' => false,
        ]);
    }

    public function inCategory(ServiceCategory $category): static
    {
        return $this->state(fn (): array => ['service_category_id' => $category->id]);
    }

    public function forService(Service $service): static
    {
        return $this->state(fn (): array => [
            'service_id' => $service->id,
            'service_category_id' => $service->service_category_id ?? ServiceCategory::factory(),
        ]);
    }
}
