<?php
namespace LaravelRocket\Generator\Generators\Services;

class ServiceUnitTestGenerator extends ServiceGenerator
{
    /**
     * @return string
     */
    protected function getPath(): string
    {
        return base_path('/tests/Services/'.$this->name.'ServiceTest.php');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return 'service.service_unittest';
    }
}
