<?php

namespace App\Observers;

use App\Models\Content;
use Illuminate\Support\Facades\Cache;

class ContentObserver
{
    private const CACHE_KEY = 'search_statistics';

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
        Cache::forget(self::CACHE_KEY);
    }
}

