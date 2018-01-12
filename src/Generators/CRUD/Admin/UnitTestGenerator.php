<?php
namespace LaravelRocket\Generator\Generators\CRUD\Admin;

use LaravelRocket\Generator\Generators\CRUD\UnitTestGenerator as BaseUnitTestGenerator;

class UnitTestGenerator extends BaseUnitTestGenerator
{
    /**
     * @return string
     */
    protected function getPath(): string
    {
        $modelName = $this->getModelName();

        return base_path('tests/Controllers/Admin/'.$modelName.'Controller.php');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return 'crud.admin.unittest';
    }
}
