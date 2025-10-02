<?php
namespace App\Services\Providers;

use App\Models\Provider;
use App\Enums\ProviderType;
use App\Services\Providers\Adapters\JsonProviderAdapter;
use App\Services\Providers\Adapters\XmlProviderAdapter;

class ProviderAdapterFactory
{
    public static function make(Provider $provider)
    {
        return match ($provider->type) {
            ProviderType::JSON->value => new JsonProviderAdapter($provider),
            ProviderType::XML->value  => new XmlProviderAdapter($provider),
            default => throw new \InvalidArgumentException("Unsupported provider type"),
        };
    }
}
