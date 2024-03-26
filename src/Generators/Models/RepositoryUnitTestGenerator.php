<?php

namespace LaravelRocket\Generator\Generators\Models;

use Illuminate\Support\Str;

class RepositoryUnitTestGenerator extends ModelBaseGenerator
{
    /**
     * @return string
     */
    protected function getPath(): string
    {
        $modelName = $this->getModelName();

        return base_path('tests/Repositories/'.$modelName.'RepositoryTest.php');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return 'repository.unittest';
    }

    /**
     * @return array
     */
    protected function getVariables(): array
    {
        $modelName                  = $this->getModelName();
        $variables                  = [];
        $variables['modelName']     = $modelName;
        $variables['variableName']  = Str::camel($modelName);
        $variables['className']     = $modelName.'RepositoryInterface';
        $variables['tableName']     = $this->table->getName();
        $variables['relationTable'] = $this->detectRelationTable($this->table);

        return $variables;
    }
}
