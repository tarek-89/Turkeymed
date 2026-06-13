<?php

namespace Database\Factories;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quote' => ['en' => fake()->sentence(14)],
            'author_name' => fake()->name(),
            'author_meta' => ['en' => fake()->randomElement(['FUE', 'Dental', 'LASIK']).' · '.fake()->country()],
            'avatar' => null,
            'rating' => 5,
            'is_featured' => false,
            'is_published' => true,
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }

    public function featured(): static
    {
        return $this->state(fn (): array => ['is_featured' => true]);
    }

    public function unpublished(): static
    {
        return $this->state(fn (): array => ['is_published' => false]);
    }
}
