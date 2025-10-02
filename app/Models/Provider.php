<?php

namespace App\Models;

use App\Enums\ProviderType;
use App\Scopes\ActiveProviderScope;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
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

}
