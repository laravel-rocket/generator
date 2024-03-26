<?php

namespace LaravelRocket\Generator\Generators\Models;

use Illuminate\Support\Str;

class ModelUnitTestGenerator extends ModelBaseGenerator
{
    /**
     * @return string
     */
    protected function getPath(): string
    {
        $modelName = $this->getModelName();

        return base_path('tests/Models/'.$modelName.'Test.php');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return 'model.unittest';
    }

    /**
     * @return array
     */
    protected function getVariables(): array
    {
        $modelName                 = $this->getModelName();
        $variables                 = [];
        $variables['modelName']    = $modelName;
        $variables['variableName'] = Str::camel($modelName);

        return $variables;
    }
}
