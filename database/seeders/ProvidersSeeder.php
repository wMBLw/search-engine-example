<?php

namespace Database\Seeders;

use App\Enums\ProviderType;
use App\Models\Provider;
use Illuminate\Database\Seeder;

class ProvidersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $providers = [
            [
                'name' => 'provider1',
                'type' => ProviderType::JSON->value,
                'endpoint' => 'https://raw.githubusercontent.com/WEG-Technology/mock/refs/heads/main/v2/provider1',
                'config' => null,
                'is_active' => true,
                'last_synced_at' => null
            ],
            [
                'name' => 'provider2',
                'type' => ProviderType::XML->value,
                'endpoint' => 'https://raw.githubusercontent.com/WEG-Technology/mock/refs/heads/main/v2/provider2',
                'config' => null,
                'is_active' => true,
                'last_synced_at' => null
            ]
        ];

        foreach ($providers as $provider) {
            Provider::updateOrCreate(
                ['name' => $provider['name']],
                $provider
            );
        }

        $this->command->info("ProvidersSeeder worked");
    }
}
