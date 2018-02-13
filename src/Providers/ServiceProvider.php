<?php
namespace LaravelRocket\Generator\Providers;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use LaravelRocket\Generator\Commands\AdminCRUDGenerator;
use LaravelRocket\Generator\Commands\GenerateFromMWB;
use LaravelRocket\Generator\Commands\HelperGenerator;
use LaravelRocket\Generator\Commands\MigrationGenerator;
use LaravelRocket\Generator\Commands\ModelGenerator;
use LaravelRocket\Generator\Commands\RepositoryGenerator;
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
            return new MigrationGenerator($app['config'], $app['files'], $app['view']);
        });

        $this->app->singleton('command.rocket.make.model', function ($app) {
            return new ModelGenerator($app['config'], $app['files'], $app['view']);
        });

        $this->app->singleton('command.rocket.make.repository', function ($app) {
            return new RepositoryGenerator($app['config'], $app['files'], $app['view']);
        });

        $this->app->singleton('command.rocket.make.crud.admin', function ($app) {
            return new AdminCRUDGenerator($app['config'], $app['files'], $app['view']);
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

        $this->commands(
            'command.rocket.make.model'
        );

        $this->commands(
            'command.rocket.make.repository'
        );

        $this->commands(
            'command.rocket.make.crud.admin'
        );
    }
}
