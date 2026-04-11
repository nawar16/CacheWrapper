<?php

namespace Nawar16\CacheWrapper;

use Illuminate\Support\ServiceProvider;

class CacheWrapperServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/CacheWrapper.php' => config_path('CacheWrapper.php'),
            ], 'config');
        }
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