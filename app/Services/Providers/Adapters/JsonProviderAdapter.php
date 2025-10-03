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
        $contentType = strtolower($item['type'] ?? 'unknown');

        $normalizedContentDTO = new NormalizedContentDTO();

        $normalizedContentDTO->setExternalId($item['id']);
        $normalizedContentDTO->setTitle($item['title']);
        $normalizedContentDTO->setType(ContentType::from($item['type']));


        if ($contentType === ContentType::VIDEO->value) {
            $this->mapVideoMetrics($normalizedContentDTO, $metrics);
        } elseif ($contentType === ContentType::ARTICLE->value) {
            $this->mapArticleMetrics($normalizedContentDTO, $metrics);
        } else {
            //maybe throw exception
            $this->mapDefaultMetrics($normalizedContentDTO, $metrics);
        }

        $normalizedContentDTO->setPublishedAt(isset($item['published_at']) ? Carbon::parse($item['published_at']) : null);

        $normalizedContentDTO->setTags($this->normalizeTags($item['tags'] ?? []));

        return $normalizedContentDTO;
    }

    private function mapVideoMetrics(NormalizedContentDTO $normalizedContentDTO, array $metrics): void
    {
        $normalizedContentDTO->setViews(isset($metrics['views']) ? (int)$metrics['views'] : 0);
        $normalizedContentDTO->setLikes(isset($metrics['likes']) ? (int)$metrics['likes'] : 0);

        $duration = $metrics['duration'] ?? null;
        if (!is_null($duration)) {
            $normalizedContentDTO->setDuringSeconds($this->durationToSeconds($duration));
        }
    }

    private function mapArticleMetrics(NormalizedContentDTO $normalizedContentDTO, array $metrics): void
    {
        $readingTime = $metrics['reading_time'] ?? null;
        if (!is_null($readingTime)) {
            $normalizedContentDTO->setReadingTime($this->durationToSeconds($readingTime));
        }

        $normalizedContentDTO->setReactions(isset($metrics['reactions']) ? (int)$metrics['reactions'] : 0);
        $normalizedContentDTO->setComments(isset($metrics['comments']) ? (int)$metrics['comments'] : 0);
    }

    private function mapDefaultMetrics(NormalizedContentDTO $normalizedContentDTO, array $metrics): void
    {
        $normalizedContentDTO->setViews(isset($metrics['views']) ? (int)$metrics['views'] : 0);
        $normalizedContentDTO->setLikes(isset($metrics['likes']) ? (int)$metrics['likes'] : 0);

        $duration = $metrics['duration'] ?? null;
        if (!is_null($duration)) {
            $normalizedContentDTO->setDuringSeconds($this->durationToSeconds($duration));
        }

        $readingTime = $metrics['reading_time'] ?? null;
        if (!is_null($readingTime)) {
            $normalizedContentDTO->setReadingTime((int)$readingTime);
        }

        $reactions = $metrics['reactions'] ?? null;
        if (!is_null($reactions)) {
            $normalizedContentDTO->setReactions((int)$reactions);
        }
    }

    private function normalizeTags(array $tags): array
    {
        return array_unique(array_filter($tags));
    }

}
