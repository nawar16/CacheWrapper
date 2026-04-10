<?php

namespace Nawar16\CacheWrapper;
use Illuminate\Support\Facades\Cache;

class CacheWrapper
{
    private array $usage = [];
    private array $ttls = [];

    public function getTtl(string $key): int
    {
        return $this->ttls[$key] ?? 60;
    }
    public function remember(string $key, callable $callback)
    {
        $this->usage[$key] = ($this->usage[$key] ?? 0) + 1;
        $ttl = $this->calculateTtl($key);
        $this->ttls[$key] = $ttl;
        return Cache::remember($key, $ttl, $callback);
    }
    public function forget(string $key): bool
    {
        unset($this->usage[$key]);
        unset($this->ttls[$key]);
        return Cache::forget($key);
    }
    private function calculateTtl(string $key): int
    {
        $usage = $this->usage[$key] ?? 1;
        $ttl = $usage > 3 ? 120:60;
        return $ttl;   
    }
    public function flush(): bool
    {
        $this->usage = [];
        $this->ttls = [];
        return Cache::flush();
    }
}