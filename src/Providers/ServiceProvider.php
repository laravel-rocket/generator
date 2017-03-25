<?php

namespace LaravelRocket\Generator\Providers;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use LaravelRocket\Generator\Console\Commands\HelperGeneratorCommand;
use LaravelRocket\Generator\Console\Commands\RepositoryGeneratorCommand;
use LaravelRocket\Generator\Console\Commands\ServiceGeneratorCommand;

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

        $this->app->singleton(
            'command.rocket.generate.service',
            function ($app) {
                return new ServiceGeneratorCommand($app['config'], $app['files'], $app['view']);
            }
        );

        $this->app->singleton(
            'command.rocket.generate.model',
            function ($app) {
                return new ServiceGeneratorCommand($app['config'], $app['files'], $app['view']);
            }
        );

        $this->app->singleton(
            'command.rocket.generate.helper',
            function ($app) {
                return new HelperGeneratorCommand($app['config'], $app['files'], $app['view']);
            }
        );

        $this->commands(
            'command.rocket.generate.repository',
            'command.rocket.generate.service',
            'command.rocket.generate.model',
            'command.rocket.generate.helper'
        );
    }

}
