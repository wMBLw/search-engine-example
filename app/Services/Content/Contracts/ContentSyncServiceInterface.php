<?php

namespace App\Services\Content\Contracts;

use App\Models\Provider;

interface ContentSyncServiceInterface
{

    public function syncProvider(Provider $provider): array;
    public function syncAllProviders(): array;
}
