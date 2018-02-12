<?php
namespace LaravelRocket\Generator\Providers;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use LaravelRocket\Generator\Commands\GenerateFromMWB;
use LaravelRocket\Generator\Commands\HelperGenerator;
use LaravelRocket\Generator\Commands\ServiceGenerator;
use LaravelRocket\Generator\Commands\ValidatorFromMWB;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->singleton('command.rocket.generate.from-mwb', function ($app) {
            return new GenerateFromMWB($app['config'], $app['files'], $app['view']);
        });

        $this->app->singleton('command.rocket.validate.from-mwb', function ($app) {
            return new ValidatorFromMWB($app['config'], $app['files'], $app['view']);
        });

        $this->app->singleton('command.rocket.make.service', function ($app) {
            return new ServiceGenerator($app['config'], $app['files'], $app['view']);
        });

        $this->app->singleton('command.rocket.make.helper', function ($app) {
            return new HelperGenerator($app['config'], $app['files'], $app['view']);
        });

        $this->app->singleton('command.rocket.make.migration', function ($app) {
            return new HelperGenerator($app['config'], $app['files'], $app['view']);
        });

        $this->commands(
            'command.rocket.generate.from-mwb'
        );

        $this->commands(
            'command.rocket.validate.from-mwb'
        );

        $this->commands(
            'command.rocket.make.service'
        );

        $this->commands(
            'command.rocket.make.helper'
        );

        $this->commands(
            'command.rocket.make.migration'
        );
    }
}
