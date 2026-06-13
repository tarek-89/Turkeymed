<?php

namespace Database\Factories;

use App\Models\SocialLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SocialLink>
 */
class SocialLinkFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $platform = fake()->randomElement(array_keys(SocialLink::PLATFORMS));

        return [
            'platform' => $platform,
            'label' => null,
            'url' => 'https://example.com/'.$platform,
            'is_published' => true,
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (): array => ['is_published' => false]);
    }

    public function platform(string $platform): static
    {
        return $this->state(fn (): array => ['platform' => $platform]);
    }
}
