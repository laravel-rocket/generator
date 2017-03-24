<?php

namespace LaravelRocket\Generator\Providers;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use LaravelRocket\Generator\Console\Commands\RepositoryGeneratorCommand;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->singleton(
            'command.rocket.generate.repository',
            function ($app) {
                return new RepositoryGeneratorCommand($app['config'], $app['files'], $app['view']);
            }
        );
    }

}