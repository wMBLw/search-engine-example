<?php

namespace Database\Factories;

use App\Enums\ProviderType;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Provider>
 */
class ProviderFactory extends Factory
{
    protected $model = Provider::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'type' => fake()->randomElement([ProviderType::JSON, ProviderType::XML]),
            'endpoint' => fake()->url(),
            'is_active' => true,
            'consecutive_failures' => 0,
            'config' => null,
        ];
    }

    /**
     * Indicate that the provider is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the provider is JSON type.
     */
    public function json(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ProviderType::JSON,
        ]);
    }

    /**
     * Indicate that the provider is XML type.
     */
    public function xml(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ProviderType::XML,
        ]);
    }
}

