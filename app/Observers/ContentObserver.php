<?php

namespace App\Observers;

use App\Models\Content;
use App\Repositories\Search\SearchRepository;
use Illuminate\Support\Facades\Cache;

class ContentObserver
{
    public function created(Content $content): void
    {
        $this->clearStatisticsCache();
    }

    public function updated(Content $content): void
    {
        $this->clearStatisticsCache();
    }

    public function deleted(Content $content): void
    {
        $this->clearStatisticsCache();
    }

    private function clearStatisticsCache(): void
    {
        Cache::forget(SearchRepository::getStatisticsCacheKey());
    }
}

