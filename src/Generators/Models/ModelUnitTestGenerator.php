<?php
namespace LaravelRocket\Generator\Generators\Models;

class ModelUnitTestGenerator extends ModelBaseGenerator
{
    /**
     * @return string
     */
    protected function getPath(): string
    {
        $modelName = $this->getModelName();

        return base_path('/tests/Models/'.$modelName.'Test.php');
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
        $variables['variableName'] = camel_case($modelName);

        return $variables;
    }
}
