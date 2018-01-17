<?php
namespace LaravelRocket\Generator\Generators\Services;

use LaravelRocket\Generator\Generators\Helpers\HelperGenerator;

class HelperUnitTestGenerator extends HelperGenerator
{
    /**
     * @return string
     */
    protected function getPath(): string
    {
        return base_path('/tests/Helpers/'.$this->name.'HelperTest.php');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return 'helper.helper_unit_test';
    }
}
