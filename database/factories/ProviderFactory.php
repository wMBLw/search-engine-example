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
        $providers = [
            [
                'name' => 'Provider 1',
                'type' => ProviderType::JSON,
                'endpoint' => 'https://raw.githubusercontent.com/WEG-Technology/mock/refs/heads/main/v1/provider1',
            ],
            [
                'name' => 'Provider 2',
                'type' => ProviderType::XML,
                'endpoint' => 'https://raw.githubusercontent.com/WEG-Technology/mock/refs/heads/main/v2/provider2'
            ]
        ];

        $selectedProvider = fake()->randomElement($providers);

        return [
            'name' => $selectedProvider['name'],
            'type' => $selectedProvider['type'],
            'endpoint' => $selectedProvider['endpoint'],
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

