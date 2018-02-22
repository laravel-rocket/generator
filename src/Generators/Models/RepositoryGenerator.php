<?php
namespace LaravelRocket\Generator\Generators\Models;

use LaravelRocket\Generator\Objects\ClassLike;

class RepositoryGenerator extends ModelBaseGenerator
{
    /**
     * @return string
     */
    protected function getPath(): string
    {
        $modelName = $this->getModelName();

        return app_path('Repositories/Eloquent/'.$modelName.'Repository.php');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return 'repository.repository';
    }

    /**
     * @return array
     */
    protected function getVariables(): array
    {
        $modelName                    = $this->getModelName();
        $variables                    = [];
        $variables['modelName']       = $modelName;
        $variables['className']       = $modelName.'Repository';
        $variables['variableName']    = camel_case($modelName);
        $variables['tableName']       = $this->table->getName();
        $variables['relationTable']   = $this->detectRelationTable($this->table);
        $variables['baseClass']       = $variables['relationTable'] ? 'RelationModelRepository' : 'SingleKeyModelRepository';
        $variables['keywordColumns']  = [];
        $variables['existingMethods'] = $this->getExistingMethods();

        foreach ($this->table->getColumns() as $column) {
            $name = $column->getName();
            if ($name === 'name' || ends_with($name, '_name') || ends_with($name, '_code')) {
                $variables['keywordColumns'][] = $name;
            }
        }

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
