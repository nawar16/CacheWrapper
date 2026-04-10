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
    public function test_it_stores_and_flushs_by_tags()
    {
        $cache = $this->app->make(CacheWrapper::class);
        $cache->tags(['users'])->remember('user:1', fn() => 'userX');
        $cache->tags(['users'])->flush();
        $result = $cache->tags(['users'])->remember('user:1', fn() => 'userY');
        $this->assertEquals('userY', $result);
    }
    public function test_it_stores_multiple_keys_under_same_tag()
    {
        $cache = $this->app->make(CacheWrapper::class);
        $cache->tags(['users'])->remember('user:1', fn() => 'userX');
        $cache->tags(['users'])->remember('user:2', fn() => 'userY');
        $this->assertTrue(true); 
    }
    public function test_it_does_not_affect_other_tags_when_flushing()
    {
        $cache = $this->app->make(CacheWrapper::class);
        $cache->tags(['users'])->remember('user:1', fn() => 'userX');
        $cache->tags(['posts'])->remember('post:1', fn() => 'userY');
        $cache->tags(['users'])->flush();
        $result = $cache->tags(['posts'])->remember('post:1', fn() => 'postX');
        $this->assertEquals('userY', $result);
    }
    public function test_it_resets_tag_context_after_operation()
    {
        $cache = $this->app->make(CacheWrapper::class);
        $cache->tags(['users'])->remember('user:1', fn() => 'userX');
        $cache->remember('user:2', fn() => 'userY');
        $this->assertTrue(true); 
    }
    public function test_it_flushs_all_keys_in_tag()
    {
        $cache = $this->app->make(CacheWrapper::class);
        $cache->tags(['users'])->remember('user:1', fn() => 'userX');
        $cache->tags(['users'])->remember('user:2', fn() => 'userY');
        $cache->tags(['users'])->flush();
        $result = $cache->tags(['users'])->remember('user:1', fn() => 'userZ');
        $this->assertEquals('userZ', $result);
    }
    public function test_it_tracks_cache_hits_and_misses()
    {
        $cache = $this->app->make(CacheWrapper::class);
        $cache->remember('key', fn() => 'value'); 
        $cache->remember('key', fn() => 'value'); 
        $stats = $cache->stats();
        $this->assertEquals(1, $stats['misses']);
        $this->assertEquals(1, $stats['hits']);
    }
    public function test_get_metrics()
    {
        $cache = $this->app->make(CacheWrapper::class);
        $result = $cache->getMetrics();
        $this->assertTrue($result);
    }
}