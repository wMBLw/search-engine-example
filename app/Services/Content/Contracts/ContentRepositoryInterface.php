<?php

namespace App\Services\Content\Contracts;

use App\Models\Content;
use App\Models\Provider;
use App\Services\Providers\NormalizedContentDTO;

interface ContentRepositoryInterface
{

    public function createOrUpdateFromDto(Provider $provider, NormalizedContentDTO $dto): Content;

    public function findByProviderAndExternalId(Provider $provider, string $externalId): ?Content;

    public function getContentCountForProvider(Provider $provider): int;

    public function deleteOldContentForProvider(Provider $provider, int $daysOld): int;
}
