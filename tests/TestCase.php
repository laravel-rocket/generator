<?php

namespace LaravelRocket\Generator\Tests;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Routing\Router;
use Illuminate\Events\Dispatcher;

class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    use WithoutMiddleware;

    /**
     * Setup DB before each test.
     */
    public function setUp()
    {
        parent::setUp();
        $this->app->boot();
    }

    /**
     * Boots the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        /** @var $app \Illuminate\Foundation\Application */
        $app = require __DIR__.'/../vendor/laravel/laravel/bootstrap/app.php';
        $this->setUpHttpKernel($app);
        $app->register(\Illuminate\Database\DatabaseServiceProvider::class);
        $app->register(\LaravelRocket\Generator\Providers\ServiceProvider::class);
        return $app;
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    private function setUpHttpKernel($app)
    {
        $app->instance('request', (new \Illuminate\Http\Request())->instance());
        $app->make('Illuminate\Foundation\Http\Kernel', [$app, $this->getRouter()])->bootstrap();
    }


    /**
     * @return Router
     */
    protected function getRouter()
    {
        $router = new Router(new Dispatcher());
        return $router;
    }
}
