<?php

namespace App\Services\Content;

use App\Models\Content;
use App\Models\Provider;
use App\Services\Content\Contracts\ContentRepositoryInterface;
use App\Services\Providers\NormalizedContentDTO;
use Carbon\Carbon;

class ContentRepository implements ContentRepositoryInterface
{
    public function createOrUpdateFromDto(Provider $provider, NormalizedContentDTO $dto): Content
    {
        return Content::updateOrCreate(
            [
                'provider_id' => $provider->id,
                'external_id' => $dto->getExternalId(),
            ],
            [
                'type' => $dto->getType(),
                'title' => $dto->getTitle(),
                'views' => $dto->getViews(),
                'likes' => $dto->getLikes(),
                'reactions' => $dto->getReactions(),
                'reading_time' => $dto->getReadingTime(),
                'during_seconds' => $dto->getDuringSeconds(),
                'published_at' => $dto->getPublishedAt(),
                'tags' => $dto->getTags()
            ]
        );
    }

    public function findByProviderAndExternalId(Provider $provider, string $externalId): ?Content
    {
        return Content::where('provider_id', $provider->id)
            ->where('external_id', $externalId)
            ->first();
    }

    public function getContentCountForProvider(Provider $provider): int
    {
        return $provider->contents()->count();
    }

    public function deleteOldContentForProvider(Provider $provider, int $daysOld): int
    {
        $cutoffDate = Carbon::now()->subDays($daysOld);

        return $provider->contents()
            ->where('created_at', '<', $cutoffDate)
            ->delete();
    }
}
