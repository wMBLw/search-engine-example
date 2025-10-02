<?php

namespace App\Services\Content\Contracts;

interface DistributedLockInterface
{
    public function acquire(string $key, int $ttlSeconds = 300): bool;
    public function release(string $key): bool;
    public function executeWithLock(string $key, callable $callback, int $ttlSeconds = 300): mixed;
}
