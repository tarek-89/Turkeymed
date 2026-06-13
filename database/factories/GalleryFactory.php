<?php

namespace Database\Factories;

use App\Models\Gallery;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Gallery>
 */
class GalleryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => ['en' => ucwords(fake()->unique()->words(2, true))],
            'description' => ['en' => fake()->sentence(8)],
            'layout' => 'grid',
            'images' => [
                'galleries/'.fake()->unique()->slug(2).'.jpg',
                'galleries/'.fake()->unique()->slug(2).'.jpg',
            ],
            'is_published' => true,
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }

    public function slider(): static
    {
        return $this->state(fn (): array => ['layout' => 'slider']);
    }

    public function unpublished(): static
    {
        return $this->state(fn (): array => ['is_published' => false]);
    }
}
