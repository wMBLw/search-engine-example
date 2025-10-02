<?php

namespace App\Services\Content;

use App\Services\Content\Contracts\DistributedLockInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class RedisDistributedLock implements DistributedLockInterface
{
    private const LOCK_PREFIX = 'content_sync_lock:';
    private array $lockValues = [];

    public function acquire(string $key, int $ttlSeconds = 300): bool
    {
        $lockKey = $this->getLockKey($key);
        $lockValue = $this->generateLockValue();

        $acquired = Cache::store('redis')->add($lockKey, $lockValue, $ttlSeconds);

        if ($acquired) {
            $this->lockValues[$key] = $lockValue;
            return true;
        }

        return false;
    }

    public function release(string $key): bool
    {
        $lockKey = $this->getLockKey($key);
        $lockValue = $this->lockValues[$key] ?? null;

        if (!$lockValue) {
            return false;
        }

        $currentValue = Cache::store('redis')->get($lockKey);

        if ($currentValue === $lockValue) {
            Cache::store('redis')->forget($lockKey);
            unset($this->lockValues[$key]);
            return true;
        }

        return false;
    }
    public function executeWithLock(string $key, callable $callback, int $ttlSeconds = 300): mixed
    {
        if (!$this->acquire($key, $ttlSeconds)) {
            throw new \RuntimeException("Could not acquire lock for key: {$key}");
        }

        try {

            return $callback();

        } finally {
            $this->release($key);
        }
    }

    private function getLockKey(string $key): string
    {
        return self::LOCK_PREFIX . $key;
    }

    private function generateLockValue(): string
    {
        return Str::uuid()->toString();
    }
}
