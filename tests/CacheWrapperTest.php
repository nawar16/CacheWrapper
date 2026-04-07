<?php

use PHPUnit\Framework\TestCase;
use nawar16\CacheWrapper\CacheWrapper;

class CacheWrapperTest extends TestCase
{
    public function test_it_stores_and_returns_value()
    {
        $cache = new CacheWrapper();

        $result = $cache->remember('key', function () {
            return 'hello';
        });

        $this->assertEquals('hello1', $result);
    }
}