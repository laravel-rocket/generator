<?php
namespace LaravelRocket\Generator\Generators\CRUD\User;

use LaravelRocket\Generator\Generators\CRUD\CRUDBaseGenerator;

class ControllerGenerator extends CRUDBaseGenerator
{
    /**
     * @return string
     */
    protected function getPath(): string
    {
        $modelName = $this->getModelName();

        return app_path('/Http/Controllers/User/'.$modelName.'Controller.php');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return 'crud.user.controller';
    }
}
