<?php

namespace App\Models;

use App\Enums\ProviderType;
use App\Scopes\ActiveProviderScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Provider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'endpoint',
        'config',
        'is_active',
        'last_synced_at',
        'disabled_until',
        'consecutive_failures',
    ];

    protected $casts = [
        'type' => ProviderType::class,
        'config' => 'json',
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
        'disabled_until' => 'datetime',
        'consecutive_failures' => 'integer',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new ActiveProviderScope);
    }

    public function scopeByNonDisabledUntil(Builder $query)
    {
        return $query->where(function ($subQuery) {
            $subQuery->whereNull('disabled_until')
                ->orWhere('disabled_until', '<', Carbon::now());
        });

    }

    public function contents(): HasMany
    {
        return $this->hasMany(Content::class);
    }
}
