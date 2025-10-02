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

    public $contentType = ProviderType::XML;

    public function fetchAll(): array
    {

        $xmlApiResponseBody = $this->getApiData();

        $xmlConvertedArray = $this->xmlStringToArray($xmlApiResponseBody);

        if (!is_array($xmlConvertedArray)) {
            return [];
        }

        return $this->convertNormalizedContentDto($xmlConvertedArray['items']['item']);
    }

    private function xmlStringToArray(string $xmlString): ?array
    {
        $xmlData = simplexml_load_string($xmlString, SimpleXMLElement::class, LIBXML_NOCDATA);

        if (is_null($xmlData)) {
            return null;
        }

        $jsonData = json_encode($xmlData);

        return json_decode($jsonData, true);
    }



    public function mapToDto(array $item): NormalizedContentDTO
    {
        $metrics = $item['stats'] ?? [];

        $normalizedContentDTO = new NormalizedContentDTO();

        $normalizedContentDTO->setExternalId($item['id']);
        $normalizedContentDTO->setTitle($item['headline']);
        $normalizedContentDTO->setType(ContentType::from($item['type']));
        $normalizedContentDTO->setViews(isset($metrics['views']) ? (int)$metrics['views'] : 0);
        $normalizedContentDTO->setLikes(isset($metrics['likes']) ? (int)$metrics['likes'] : 0);

        $duration = $metrics['duration'] ?? null;
        if (!is_null($duration)) {
            $normalizedContentDTO->setDuringSeconds($this->durationToSeconds($duration));
        }

        $normalizedContentDTO->setPublishedAt(isset($item['publication_date']) ? Carbon::parse($item['publication_date']) : null);

        $normalizedContentDTO->setTags($item['categories']);

        return $normalizedContentDTO;
    }
}
