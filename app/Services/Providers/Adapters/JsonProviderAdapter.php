<?php

namespace App\Services\Providers\Adapters;

use App\Enums\ContentType;
use App\Enums\ProviderType;
use App\Services\Providers\AbstractProviderAdapter;
use App\Services\Providers\NormalizedContentDTO;
use Carbon\Carbon;

class JsonProviderAdapter extends AbstractProviderAdapter
{
    public ProviderType $providerType = ProviderType::JSON;

    public function fetchAll(): array
    {
        try {

            $apiResponseBody = $this->getApiData();
            $jsonApiResponseBody = json_decode($apiResponseBody, true);

            if (!is_array($jsonApiResponseBody)){
                return [];
            }

        }catch (\Throwable $e) {
            report($e);
            return [];
        }

        return $this->convertNormalizedContentDto($jsonApiResponseBody['contents']);

    }

    public function mapToDto(array $item): NormalizedContentDTO
    {
        $metrics = $item['metrics'] ?? [];

        $normalizedContentDTO = new NormalizedContentDTO();

        $normalizedContentDTO->setExternalId($item['id']);
        $normalizedContentDTO->setTitle($item['title']);
        $normalizedContentDTO->setType(ContentType::from($item['type']));
        $normalizedContentDTO->setViews(isset($metrics['views']) ? (int)$metrics['views'] : 0);
        $normalizedContentDTO->setLikes(isset($metrics['likes']) ? (int)$metrics['likes'] : 0);

        $duration = $metrics['duration'] ?? null;
        if (!is_null($duration)) {
            $normalizedContentDTO->setDuringSeconds($this->durationToSeconds($duration));
        }

        $normalizedContentDTO->setPublishedAt(isset($item['published_at']) ? Carbon::parse($item['published_at']) : null);

        $normalizedContentDTO->setTags($item['tags']);

        return $normalizedContentDTO;
    }

}
