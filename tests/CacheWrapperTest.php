<?php

use Orchestra\Testbench\TestCase;
use Nawar16\CacheWrapper\CacheWrapper;

class CacheWrapperTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('cache.default', 'array');
    }

    public function test_it_stores_and_returns_value()
    {
        $cache = new CacheWrapper();

        $result = $cache->remember('key', function () {
            return 'hello';
        });

        $this->assertEquals('hello', $result);
    }
}