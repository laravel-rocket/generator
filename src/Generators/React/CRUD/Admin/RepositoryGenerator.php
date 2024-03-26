<?php

namespace LaravelRocket\Generator\Generators\React\CRUD\Admin;

use LaravelRocket\Generator\Generators\React\CRUD\ReactCRUDBaseGenerator;

class RepositoryGenerator extends ReactCRUDBaseGenerator
{
    /**
     * @return string
     */
    protected function getPath(): string
    {
        $modelName = $this->getModelName();

        return resource_path('assets/admin/src/repositories/'.$modelName.'Repository.js');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return 'react.crud.admin.repository';
    }
}
