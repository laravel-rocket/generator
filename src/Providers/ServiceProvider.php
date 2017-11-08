<?php
namespace LaravelRocket\Generator\Providers;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use LaravelRocket\Generator\Console\Commands\AdminCRUDGeneratorCommand;
use LaravelRocket\Generator\Console\Commands\AlterMigrationGeneratorCommand;
use LaravelRocket\Generator\Console\Commands\APIGeneratorCommand;
use LaravelRocket\Generator\Console\Commands\CreateMigrationGeneratorCommand;
use LaravelRocket\Generator\Console\Commands\HelperGeneratorCommand;
use LaravelRocket\Generator\Console\Commands\ModelGeneratorCommand;
use LaravelRocket\Generator\Console\Commands\RepositoryGeneratorCommand;
use LaravelRocket\Generator\Console\Commands\ServiceGeneratorCommand;

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
        $this->app->singleton('command.rocket.make.repository', function ($app) {
            return new RepositoryGeneratorCommand($app['config'], $app['files'], $app['view']);
        });

        $this->app->singleton('command.rocket.make.service', function ($app) {
            return new ServiceGeneratorCommand($app['config'], $app['files'], $app['view']);
        });

        $this->app->singleton('command.rocket.model.make', function ($app) {
            return new ModelGeneratorCommand($app['config'], $app['files'], $app['view']);
        });

        $this->app->singleton('command.rocket.make.helper', function ($app) {
            return new HelperGeneratorCommand($app['config'], $app['files'], $app['view']);
        });

        $this->app->singleton('command.rocket.make.migration.create', function ($app) {
            return new CreateMigrationGeneratorCommand($app['config'], $app['files'], $app['view']);
        });

        $this->app->singleton('command.rocket.make.migration.alter', function ($app) {
            return new AlterMigrationGeneratorCommand($app['config'], $app['files'], $app['view']);
        });

        $this->app->singleton('command.rocket.make.admin.crud', function ($app) {
            return new AdminCRUDGeneratorCommand($app['config'], $app['files'], $app['view']);
        });

        $this->app->singleton('command.rocket.make.api', function ($app) {
            return new APIGeneratorCommand($app['config'], $app['files'], $app['view']);
        });

        $this->commands(
            'command.rocket.make.repository',
            'command.rocket.make.service',
            'command.rocket.model.make',
            'command.rocket.make.helper',
            'command.rocket.make.migration.create',
            'command.rocket.make.migration.alter',
            'command.rocket.make.admin.crud',
            'command.rocket.make.api'
        );
    }
}
