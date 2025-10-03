<?php

namespace Database\Factories;

use App\Enums\ContentType;
use App\Models\Content;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Content>
 */
class ContentFactory extends Factory
{
    protected $model = Content::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement([ContentType::VIDEO, ContentType::ARTICLE]);

        return [
            'provider_id' => Provider::factory(),
            'external_id' => fake()->uuid(),
            'type' => $type,
            'title' => fake()->sentence(),
            'views' => fake()->numberBetween(100, 100000),
            'likes' => fake()->numberBetween(10, 5000),
            'comments' => fake()->numberBetween(0, 500),
            'reactions' => fake()->numberBetween(0, 1000),
            'reading_time' => $type === ContentType::ARTICLE ? fake()->numberBetween(1, 20) : 0,
            'during_seconds' => $type === ContentType::VIDEO ? fake()->numberBetween(60, 3600) : 0,
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'tags' => fake()->randomElements(['php', 'laravel', 'javascript', 'testing', 'programming'], fake()->numberBetween(1, 3)),
        ];
    }

    /**
     * Indicate that the content is a video.
     */
    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ContentType::VIDEO,
            'during_seconds' => fake()->numberBetween(60, 3600),
            'reading_time' => 0,
        ]);
    }

    /**
     * Indicate that the content is an article.
     */
    public function article(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ContentType::ARTICLE,
            'reading_time' => fake()->numberBetween(1, 20),
            'during_seconds' => 0,
        ]);
    }

    /**
     * Indicate that the content was published recently.
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'published_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Indicate that the content is popular.
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'views' => fake()->numberBetween(50000, 500000),
            'likes' => fake()->numberBetween(5000, 50000),
            'comments' => fake()->numberBetween(500, 5000),
        ]);
    }
}

