<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatisticsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'total_contents' => $this->resource['total_contents'],
            'total_videos' => $this->resource['total_videos'],
            'total_articles' => $this->resource['total_articles'],
            'total_views' => $this->resource['total_views'],
            'total_likes' => $this->resource['total_likes'],
            'total_comments' => $this->resource['total_comments'],
            'contents_by_provider' => $this->resource['contents_by_provider'],
            'recent_activity' => $this->resource['recent_activity'],
            'cached_at' => $this->resource['cached_at'] ?? null,
        ];
    }
}

