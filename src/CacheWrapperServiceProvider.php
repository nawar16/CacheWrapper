<?php

namespace Nawar16\CacheWrapper;

use Illuminate\Support\ServiceProvider;

class CacheWrapperServiceProvider extends ServiceProvider
{
    public function boot()
    {}

    public function register()
    {
        $this->app->singleton(CacheWrapper::class, function () {
            return new CacheWrapper();
        });
    }

}