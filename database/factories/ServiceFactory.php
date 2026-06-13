<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Default state: a published English service without a category
     * (matching imports where the category may be missing).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = ucwords(fake()->unique()->words(3, true));

        return [
            'service_category_id' => null,
            'translation_group_id' => null,
            'language' => Post::DEFAULT_LANGUAGE,
            'slug' => Str::slug($title),
            'title' => $title,
            'excerpt' => fake()->sentence(12),
            'body' => '<h2>'.fake()->sentence(3).'</h2><p>'.implode('</p><p>', fake()->paragraphs(3)).'</p>',
            'featured_image' => null,
            'author' => fake()->name(),
            'meta_title' => null,
            'meta_description' => null,
            'focus_keyword' => null,
            'is_elementor' => false,
            'status' => 'publish',
            'published_at' => fake()->dateTimeBetween('-1 year', '-1 day'),
            'wp_modified_at' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (): array => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    public function language(string $language): static
    {
        return $this->state(fn (): array => ['language' => $language]);
    }

    public function inCategory(ServiceCategory $category): static
    {
        return $this->state(fn (): array => ['service_category_id' => $category->id]);
    }

    public function inTranslationGroup(int $groupId): static
    {
        return $this->state(fn (): array => ['translation_group_id' => $groupId]);
    }

    public function withFeaturedImage(): static
    {
        return $this->state(fn (): array => [
            'featured_image' => fake()->numberBetween(2020, 2026).'/0'.fake()->numberBetween(1, 9).'/'.fake()->slug(2).'.jpg',
        ]);
    }
}
