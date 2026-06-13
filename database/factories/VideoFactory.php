<?php

namespace Database\Factories;

use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Video>
 */
class VideoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => ['en' => ucwords(fake()->words(3, true))],
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'kind' => 'video',
            'is_published' => true,
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }

    public function short(): static
    {
        return $this->state(fn (): array => [
            'kind' => 'short',
            'youtube_url' => 'https://www.youtube.com/shorts/dQw4w9WgXcQ',
        ]);
    }

    public function unpublished(): static
    {
        return $this->state(fn (): array => ['is_published' => false]);
    }
}
