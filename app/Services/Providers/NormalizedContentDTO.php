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
    private int $comments = 0;
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

    public function getComments(): int
    {
        return $this->comments;
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

    public function setComments(int $comments): void
    {
        $this->comments = $comments;
    }

    public function toArray(): array
    {
        return [
            'external_id' => $this->getExternalId(),
            'type' => $this->getType(),
            'title' => $this->getTitle(),
            'views' => $this->getViews(),
            'likes' => $this->getLikes(),
            'reactions' => $this->getReactions(),
            'reading_time' => $this->getReadingTime(),
            'during_seconds' => $this->getDuringSeconds(),
            'comments' => $this->getComments(),
            'published_at' => $this->getPublishedAt()?->toDateTimeString(),
            'tags' => $this->tags
        ];
    }

}
