<?php
namespace LaravelRocket\Generator\Generators\Services;

class ServiceInterfaceGenerator extends ServiceGenerator
{
    /**
     * @return string
     */
    protected function getPath(): string
    {
        return app_path('Services/'.$this->name.'ServiceInterface.php');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return 'service.service_interface';
    }
}
