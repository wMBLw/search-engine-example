<?php

namespace App\Services\Providers;

use App\Enums\ProviderType;
use App\Models\Provider;
use App\Services\Providers\Contracts\ProviderAdapterInterface;
use GuzzleHttp\Client;

abstract class AbstractProviderAdapter implements ProviderAdapterInterface
{

    protected Client $client;
    private int $defaultTimeout = 15;
    protected Provider $provider;

    public function __construct(Provider $provider)
    {
        $this->provider = $provider;

        $this->client = new Client([
            'timeout' => $this->defaultTimeout, //maybe should be moved to config or env or provider config
        ]);
    }

    public function fetchOne(string $externalId): ?NormalizedContentDTO
    {
        //This is definitely not the case. Specific API required.
        return collect($this->fetchAll())
            ->where('external_id', $externalId)
            ->first();

    }

    protected function convertNormalizedContentDto(array $items): array
    {
        $normalizedContentDto = [];
        foreach ($items as $item) {
            $normalizedContentDto[] = $this->mapToDto($item);
        }

        return $normalizedContentDto;
    }

    protected function getApiData(): string
    {
        $apiResponse = $this->client->get($this->provider->endpoint,[
            'headers' => [
                'Accept' => $this->providerType->value == ProviderType::JSON->value ? 'application/json' : 'application/xml'
            ]
        ]);

        return (string) $apiResponse->getBody();
    }

    protected function durationToSeconds(string $duration): int
    {
        $parts = array_map('intval', explode(':', $duration));
        if (count($parts) === 2) {
            return $parts[0] * 60 + $parts[1];
        } elseif (count($parts) === 3) {
            return $parts[0] * 3600 + $parts[1] * 60 + $parts[2];
        }
        return (int)$duration;
    }

}
