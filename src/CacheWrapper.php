<?php

namespace Nawar16\CacheWrapper;
use Illuminate\Support\Facades\Cache;

class CacheWrapper
{
    private array $usage = [];
    private array $ttls = [];
    private array $tags = [];
    private array $tagMap = [];
    private array $stats = [
        'hits' => 0,
        'misses' => 0
    ];

    public function getTtl(string $key): int
    {
        return $this->ttls[$key] ?? 60;
    }
    private function calculateTtl(string $key): int
    {
        $usage = $this->usage[$key] ?? 1;
        $ttl = $usage > 3 ? 120:60;
        return $ttl;   
    }
    public function tags(array $tags): self 
    {
        $this->tags = $tags;
        return $this;
    }
    public function stats(): array
    {
        return $this->stats;
    }
    public function remember(string $key, callable $callback)
    {
        $this->usage[$key] = ($this->usage[$key] ?? 0) + 1;
        $ttl = $this->calculateTtl($key);
        $this->ttls[$key] = $ttl;
        if (Cache::has($key)) {
            $this->stats['hits']++;
        } else {
            $this->stats['misses']++;
        }
        if (!empty($this->tags)) {
            foreach ($this->tags as $tag) {
                if (!isset($this->tagMap[$tag])) {
                    $this->tagMap[$tag] = [];
                }
                if (!in_array($key, $this->tagMap[$tag])) {
                    $this->tagMap[$tag][] = $key;
                }
            }
        }
        $this->tags = [];
        return Cache::remember($key, $ttl, $callback);
    }
    public function forget(string $key): bool
    {
        unset($this->usage[$key]);
        unset($this->ttls[$key]);
        return Cache::forget($key);
    }
    public function flush(): bool
    {
        if (!empty($this->tags)) {
            foreach ($this->tags as $tag) {
                if (isset($this->tagMap[$tag])) {
                    foreach ($this->tagMap[$tag] as $key) {
                        Cache::forget($key);
                    }
                }
                unset($this->tagMap[$tag]);
            }
            return true;
        }
        $this->usage = [];
        $this->ttls = [];
        return Cache::flush();
    }
}