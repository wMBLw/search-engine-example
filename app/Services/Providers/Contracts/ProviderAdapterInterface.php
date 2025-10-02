<?php

namespace App\Services\Providers\Contracts;

use App\Services\Providers\NormalizedContentDTO;

interface ProviderAdapterInterface
{
    public function fetchAll(): array;
    public function fetchOne(string $externalId): ?NormalizedContentDTO;
    public function mapToDto(array $item): NormalizedContentDTO;

}
