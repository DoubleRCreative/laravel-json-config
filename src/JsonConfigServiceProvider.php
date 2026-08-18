<?php

namespace DRC\JsonConfig;

use Illuminate\Support\ServiceProvider;

class JsonConfigServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/jsonconfig.php' => config_path('jsonconfig.php'),
            ], 'jsonconfig-config');
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/jsonconfig.php',
            'jsonconfig'
        );
    }
}
