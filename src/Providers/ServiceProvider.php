<?php
namespace LaravelRocket\Generator\Providers;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use LaravelRocket\Generator\Commands\GenerateFromMWB;

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

        $this->commands(
            'command.rocket.generate.from-mwb'
        );
    }
}
