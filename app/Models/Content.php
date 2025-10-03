<?php

namespace App\Models;

use App\Enums\ContentType;
use App\Observers\ContentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([ContentObserver::class])]
class Content extends Model
{
    use HasFactory;
    protected $fillable = [
        'provider_id',
        'external_id',
        'type',
        'title',
        'views',
        'likes',
        'reactions',
        'reading_time',
        'comments',
        'during_seconds',
        'published_at',
        'tags'
    ];

    protected $casts = [
        'type' => ContentType::class,
        'views' => 'integer',
        'likes' => 'integer',
        'reactions' => 'integer',
        'reading_time' => 'integer',
        'during_seconds' => 'integer',
        'published_at' => 'datetime',
        'tags' => 'json'
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
