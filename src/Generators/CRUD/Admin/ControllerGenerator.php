<?php
namespace LaravelRocket\Generator\Generators\CRUD\Admin;

use LaravelRocket\Generator\Generators\CRUD\CRUDBaseGenerator;

class ControllerGenerator extends CRUDBaseGenerator
{
    /**
     * @return string
     */
    protected function getPath(): string
    {
        $modelName = $this->getModelName();

        return app_path('/Http/Controllers/Admin/'.$modelName.'Controller.php');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return 'crud.admin.controller';
    }
}
