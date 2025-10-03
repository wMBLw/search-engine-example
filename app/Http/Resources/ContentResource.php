<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'external_id' => $this->external_id,
            'title' => $this->title,
            'type' => $this->type->value,
            'provider' => [
                'id' => $this->provider->id,
                'name' => $this->provider->name,
            ],
            'metrics' => [
                'views' => $this->views,
                'likes' => $this->likes,
                'comments' => $this->comments,
                'reactions' => $this->reactions,
            ],
            'scores' => [
                'total' => $this->score,
                'base_score' => $this->base_score,
                'type_coefficient' => $this->type_coefficient,
                'freshness_score' => $this->freshness_score,
                'engagement_score' => $this->engagement_score,
            ],
            'reading_time' => $this->reading_time,
            'during_seconds' => $this->during_seconds,
            'published_at' => $this->published_at,
            'tags' => $this->tags,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

