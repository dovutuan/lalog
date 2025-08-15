<?php

namespace Dovutuan\Lalog;

use Dovutuan\Lalog\Services\QueryLogger;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class ServiceProvider extends IlluminateServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/Config/lalog.php', 'lalog');

        $this->app->singleton('lalog', function () {
            return new QueryLogger();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        $this->publishes([__DIR__ . '/Config/lalog.php' => config_path('lalog.php')], 'lalog');

        if (config('lalog.enabled')) {
            $this->app->make('lalog')->register();
        }
    }
}
