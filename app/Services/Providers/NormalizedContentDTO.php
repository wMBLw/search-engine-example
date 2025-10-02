<?php

namespace App\Services\Providers;

use App\Enums\ContentType;
use Carbon\Carbon;

final class NormalizedContentDTO
{
    private string $external_id;
    private ContentType $type;
    private ?string $title = null;
    private int $views = 0;
    private int $likes = 0;
    private int $reactions = 0;
    private int $reading_time = 0;
    private int $during_seconds = 0;
    private ?Carbon $published_at = null;
    private array $tags = [];

    public function getExternalId(): string
    {
        return $this->external_id;
    }

    public function getType(): ContentType
    {
        return $this->type;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getViews(): int
    {
        return $this->views;
    }

    public function getLikes(): int
    {
        return $this->likes;
    }

    public function getReactions(): int
    {
        return $this->reactions;
    }

    public function getReadingTime(): int
    {
        return $this->reading_time;
    }

    public function getDuringSeconds(): int
    {
        return $this->during_seconds;
    }

    public function getPublishedAt(): ?Carbon
    {
        return $this->published_at;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setExternalId(string $external_id): void
    {
        $this->external_id = $external_id;
    }

    public function setType(ContentType $type): void
    {
        $this->type = $type;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function setViews(int $views): void
    {
        $this->views = $views;
    }

    public function setLikes(int $likes): void
    {
        $this->likes = $likes;
    }

    public function setReactions(int $reactions): void
    {
        $this->reactions = $reactions;
    }

    public function setReadingTime(int $reading_time): void
    {
        $this->reading_time = $reading_time;
    }

    public function setDuringSeconds(int $during_seconds): void
    {
        $this->during_seconds = $during_seconds;
    }

    public function setPublishedAt(?Carbon $published_at): void
    {
        $this->published_at = $published_at;
    }

    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }

    public function toArray(): array
    {
        return [
            'external_id' => $this->external_id,
            'type' => $this->type,
            'title' => $this->title,
            'views' => $this->views,
            'likes' => $this->likes,
            'reactions' => $this->reactions,
            'reading_time' => $this->reading_time,
            'during_seconds' => $this->during_seconds,
            'published_at' => $this->published_at?->toDateTimeString(),
            'tags' => $this->tags
        ];
    }

}
