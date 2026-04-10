<?php

use Orchestra\Testbench\TestCase;
use Nawar16\CacheWrapper\CacheWrapper;
use Nawar16\CacheWrapper\CacheWrapperServiceProvider;

class CacheWrapperTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('cache.default', 'array');
    }
    protected function getPackageProviders($app)
    {
        return [
           CacheWrapperServiceProvider::class
        ];
    }
    public function test_it_stores_and_returns_value()
    {
        $cache = new CacheWrapper();
        $result = $cache->remember('key', function () {
            return 'hello';
        });
        $this->assertEquals('hello', $result);
    }

    public function test_it_can_be_resolved_from_container()
    {
        $instance = $this->app->make(CacheWrapper::class);
        $this->assertInstanceOf(CacheWrapper::class, $instance);
    }

    public function test_it_increases_ttl_for_mostly_used_keys()
    {
        $cache = $this->app->make(CacheWrapper::class);
        for ($i = 0; $i < 15; $i++) {
            $cache->remember('popular-key', fn() => 'value');
        }
        $ttl = $cache->getTtl('popular-key');
        $this->assertTrue($ttl > 60);
    }

    public function test_it_forgets_cached_values()
    {
        $cache = $this->app->make(CacheWrapper::class);
        $cache->remember('key', fn() => 'value');
        $cache->forget('key');
        $result = $cache->remember('key', fn() => 'new-value');
        $this->assertEquals('new-value', $result);
    }
    public function test_it_flushs_all_cache()
    {
        $cache = $this->app->make(CacheWrapper::class);
        $cache->remember('xkey', fn() => 'xval');
        $cache->remember('ykey', fn() => 'yval');
        $cache->flush();
        $result = $cache->remember('xkey', fn() => 'newXVal');
        $this->assertEquals('newXVal', $result);
    }
}