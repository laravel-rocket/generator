<?php
namespace LaravelRocket\Generator\Generators\Services;

use LaravelRocket\Generator\Generators\Helpers\HelperGenerator;

class HelperInterfaceGenerator extends HelperGenerator
{
    /**
     * @return string
     */
    protected function getPath(): string
    {
        return app_path('/Helpers/'.$this->name.'HelperInterface.php');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return 'helper.helper_interface';
    }
}
