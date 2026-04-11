<?php

namespace Nawar16\CacheWrapper;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Builder;

class CacheWrapperServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/CacheWrapper.php' => config_path('CacheWrapper.php'),
            ], 'config');
        }
        Builder::macro('cache', function ($ttl = null) {
            $key = md5($this->toSql() . serialize($this->getBindings()));
            return app('cache-wrapper')->remember($key, function () {
                return $this->get();
            });
        });
    }

    public function register()
    {
        $this->app->singleton(CacheWrapper::class, function () {
            return new CacheWrapper();
        });
        $this->mergeConfigFrom(
            __DIR__.'/../config/CacheWrapper.php', 'CacheWrapper'
        );
    }

}