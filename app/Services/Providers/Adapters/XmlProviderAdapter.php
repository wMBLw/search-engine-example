<?php
namespace App\Services\Providers\Adapters;

use App\Enums\ContentType;
use App\Enums\ProviderType;
use App\Services\Providers\AbstractProviderAdapter;
use App\Services\Providers\NormalizedContentDTO;
use SimpleXMLElement;
use Carbon\Carbon;

class XmlProviderAdapter extends AbstractProviderAdapter
{
    public ProviderType $providerType = ProviderType::XML;

    public function fetchAll(): array
    {
        try {
            $xmlApiResponseBody = $this->getApiData();

            $xmlConvertedArray = $this->xmlStringToArray($xmlApiResponseBody);

            if (!is_array($xmlConvertedArray)) {
                return [];
            }

            return $this->convertNormalizedContentDto($xmlConvertedArray['items']['item']);

        } catch (\Throwable $e) {
            report($e);
            return [];
        }
    }

    private function xmlStringToArray(string $xmlString): ?array
    {
        $xmlData = simplexml_load_string($xmlString, SimpleXMLElement::class, LIBXML_NOCDATA);

        if ($xmlData === false) {
            return null;
        }

        $jsonData = json_encode($xmlData);

        return json_decode($jsonData, true);
    }



    public function mapToDto(array $item): NormalizedContentDTO
    {
        $metrics = $item['stats'] ?? [];
        $contentType = strtolower($item['type'] ?? 'unknown');

        $normalizedContentDTO = new NormalizedContentDTO();

        $normalizedContentDTO->setExternalId($item['id']);
        $normalizedContentDTO->setTitle($item['headline']);
        $normalizedContentDTO->setType(ContentType::from($item['type']));

        if ($contentType === ContentType::VIDEO->value) {
            $this->mapVideoMetrics($normalizedContentDTO, $metrics);
        } elseif ($contentType === ContentType::ARTICLE->value) {
            $this->mapArticleMetrics($normalizedContentDTO, $metrics);
        } else {
            //maybe throw exception
            $this->mapDefaultMetrics($normalizedContentDTO, $metrics);
        }

        $normalizedContentDTO->setPublishedAt(
            isset($item['publication_date']) ? Carbon::parse($item['publication_date']) : null
        );

        $normalizedContentDTO->setTags($this->normalizeTags($item['categories'] ?? []));

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
            $normalizedContentDTO->setReadingTime((int)$readingTime);
            $normalizedContentDTO->setDuringSeconds((int)$readingTime * 60);
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
        $normalizedTags['category'] = [];
        foreach ($tags as $tag) {
            if (is_array($tag)) {
                $normalizedTags['category'] = array_merge($normalizedTags['category'], array_values($tag));
            } else {
                $normalizedTags['category'][] = $tag;
            }
        }
        return array_unique(array_filter($normalizedTags));
    }
}
