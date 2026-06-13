<?php

namespace Database\Factories;

use App\Models\Office;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Office>
 */
class OfficeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'country' => ['en' => fake()->country()],
            'name' => ['en' => fake()->city().' · '.fake()->streetName()],
            'address' => ['en' => fake()->address()],
            'hours' => ['en' => 'Mon–Sat · 09:00–19:00'],
            'badge' => null,
            'phone' => fake()->e164PhoneNumber(),
            'directions_url' => 'https://maps.google.com/?q='.fake()->latitude().','.fake()->longitude(),
            'is_published' => true,
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (): array => ['is_published' => false]);
    }

    /**
     * @param  array<string, string>  $country
     */
    public function inCountry(array $country): static
    {
        return $this->state(fn (): array => ['country' => $country]);
    }
}
