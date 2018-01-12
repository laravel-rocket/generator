<?php
namespace LaravelRocket\Generator\Generators\CRUD\Admin;

use LaravelRocket\Generator\Generators\CRUD\CRUDBaseGenerator;

class RequestGenerator extends CRUDBaseGenerator
{
    /**
     * @return string
     */
    protected function getPath(): string
    {
        $modelName = $this->getModelName();

        return app_path('/Http/Requests/Admin/'.$modelName.'Request.php');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return 'crud.admin.request';
    }
}
