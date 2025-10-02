<?php
namespace App\Services\Providers;

use App\Models\Provider;
use App\Enums\ProviderType;
use GuzzleHttp\Client;

class ProviderAdapterFactory
{
    /**
     * @param Provider $provider
     * @param Client|null $client Optional Guzzle client override (for tests)
     * @return \App\Services\Providers\Contracts\ProviderAdapterInterface
     */
    public static function make(Provider $provider, ?Client $client = null)
    {
        $type = $provider->type instanceof ProviderType ? $provider->type->value : (string)$provider->type;

        return match ($type) {
            ProviderType::JSON->value => new JsonProviderAdapter($provider, $client),
            ProviderType::XML->value  => new XmlProviderAdapter($provider, $client),
            default => throw new \InvalidArgumentException("Unsupported provider type: {$type}"),
        };
    }
}
