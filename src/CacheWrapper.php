<?php

namespace Nawar16\CacheWrapper;
use Illuminate\Support\Facades\Cache;

class CacheWrapper
{
    public function remember(string $key, callable $callback)
    {
        return Cache::remember($key, 60, $callback);
    }
}