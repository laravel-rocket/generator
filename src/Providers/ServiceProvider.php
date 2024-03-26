<?php

namespace LaravelRocket\Generator\Providers;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use LaravelRocket\Generator\Commands\AdminAPIGenerator;
use LaravelRocket\Generator\Commands\AdminCRUDGenerator;
use LaravelRocket\Generator\Commands\EventGenerator;
use LaravelRocket\Generator\Commands\GenerateAPIFromOAS;
use LaravelRocket\Generator\Commands\GenerateFromMWB;
use LaravelRocket\Generator\Commands\HelperGenerator;
use LaravelRocket\Generator\Commands\MigrationGenerator;
use LaravelRocket\Generator\Commands\ModelGenerator;
use LaravelRocket\Generator\Commands\RepositoryGenerator;
use LaravelRocket\Generator\Commands\ServiceGenerator;
use LaravelRocket\Generator\Commands\Validate;
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

        $this->app->singleton('command.rocket.generate.api.from-oas', function ($app) {
            return new GenerateAPIFromOAS($app['config'], $app['files'], $app['view']);
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

        $this->app->singleton('command.rocket.validate', function ($app) {
            return new Validate($app['config'], $app['files'], $app['view']);
        });

        $this->app->singleton('command.rocket.make.event', function ($app) {
            return new EventGenerator($app['config'], $app['files'], $app['view']);
        });

        $this->app->singleton('command.rocket.make.api.admin', function ($app) {
            return new AdminAPIGenerator($app['config'], $app['files'], $app['view']);
        });

        $this->commands(
            'command.rocket.generate.from-mwb'
        );

        $this->commands(
            'command.rocket.generate.api.from-oas'
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
            'command.rocket.make.event'
        );

        $this->commands(
            'command.rocket.make.crud.admin'
        );

        $this->commands(
            'command.rocket.make.api.admin'
        );

        $this->commands(
            'command.rocket.validate'
        );
    }
}
