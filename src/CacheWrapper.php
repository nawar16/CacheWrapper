<?php

namespace Nawar16\CacheWrapper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

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

    private function canUseRedis(): bool
    {
        return config('CacheWrapper.use_redis_tags')
            && class_exists(Redis::class);
    }
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
    public function exportMetrics(): array
    {
        return [
            'hits' => $this->stats['hits'],
            'misses' => $this->stats['misses'],
            'usage' => $this->usage,
            'ttls' => $this->ttls
        ];
    }
    public function getMetrics(): bool
    {
        $metrics = $this->exportMetrics();
        //TODO: make it a request
        file_put_contents(
            storage_path('cache_metrics.json'),
            json_encode($metrics, JSON_PRETTY_PRINT)
        );
        return true;
    }
    private function shouldCompress($value): bool
    {
        return strlen(serialize($value)) > config('CacheWrapper.compression_threshold');
    }
    
    private function compress($value): string
    {
        return gzcompress(serialize($value));
    }
    
    private function decompress($value)
    {
        return unserialize(gzuncompress($value));
    }
    public function getRaw(string $key)
    {
        return Cache::get($key);
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
                if ($this->canUseRedis()) {
                    Redis::sadd("tag:$tag", $key);
                } else {
                    if (!isset($this->tagMap[$tag])) {
                        $this->tagMap[$tag] = [];
                    }
                    if (!in_array($key, $this->tagMap[$tag])) {
                        $this->tagMap[$tag][] = $key;
                    }
                }
            }
        }
        $this->tags = [];
        $val = Cache::remember($key, $ttl, function () use ($callback, $key) {
            $result = $callback();
            if ($this->shouldCompress($result)) {
                return [
                    '__compressed' => true,
                    'data' => $this->compress($result)
                ];
            }
            return $result;
        });
        if (is_array($val) && isset($val['__compressed'])) {
            $val = $this->decompress($val['data']);
        }
        return $val;
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
                if ($this->canUseRedis()) {
                    $keys = Redis::smembers("tag:$tag");
                    foreach ($keys as $key) {
                        Cache::forget($key);
                    }
                    Redis::del("tag:$tag");
                } else {
                    if (isset($this->tagMap[$tag])) {
                        foreach ($this->tagMap[$tag] as $key) {
                            Cache::forget($key);
                        }
                        unset($this->tagMap[$tag]);
                    }
                }
            }
            return true;
        }
        return Cache::flush();
    }
}