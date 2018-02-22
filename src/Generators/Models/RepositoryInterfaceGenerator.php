<?php
namespace LaravelRocket\Generator\Generators\Models;

use LaravelRocket\Generator\Objects\ClassLike;

class RepositoryInterfaceGenerator extends ModelBaseGenerator
{
    /**
     * @return string
     */
    protected function getPath(): string
    {
        $modelName = $this->getModelName();

        return app_path('Repositories/'.$modelName.'RepositoryInterface.php');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return 'repository.repository_interface';
    }

    /**
     * @return array
     */
    protected function getVariables(): array
    {
        $modelName                    = $this->getModelName();
        $variables                    = [];
        $variables['modelName']       = $modelName;
        $variables['variableName']    = camel_case($modelName);
        $variables['className']       = $modelName.'RepositoryInterface';
        $variables['tableName']       = $this->table->getName();
        $variables['relationTable']   = $this->detectRelationTable($this->table);
        $variables['baseClass']       = $variables['relationTable'] ? 'RelationModelRepository' : 'SingleKeyModelRepository';
        $variables['existingMethods'] = $this->getExistingMethods();

        return $variables;
    }

    protected function getExistingMethods(): array
    {
        if (!file_exists($this->getPath())) {
            return [];
        }

        $class = new ClassLike($this->getPath());

        $methods       =  $class->getMethods();
        $prettyPrinter = new \PhpParser\PrettyPrinter\Standard;
        $result        = [];
        foreach ($methods as $name => $method) {
            $result[$name] = $prettyPrinter->prettyPrint([$method]);
        }

        return $result;
    }
}
