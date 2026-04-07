<?php

namespace Nawar16\CacheWrapper;

class CacheWrapper
{
    private array $store = [];

    public function remember(string $key, callable $callback)
    {
        if (!isset($this->store[$key])) {
            $this->store[$key] = $callback();
        }

        return $this->store[$key];
    }
}