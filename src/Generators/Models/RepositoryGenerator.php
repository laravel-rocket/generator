<?php
namespace LaravelRocket\Generator\Generators\Models;

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
        $modelName                   = $this->getModelName();
        $variables                   = [];
        $variables['modelName']      = $modelName;
        $variables['className']      = $modelName.'Repository';
        $variables['variableName']   = camel_case($modelName);
        $variables['tableName']      = $this->table->getName();
        $variables['relationTable']  = $this->detectRelationTable($this->table);
        $variables['baseClass']      = $variables['relationTable'] ? 'RelationModelRepository' : 'SingleKeyModelRepository';
        $variables['keywordColumns'] = [];

        foreach ($this->table->getColumns() as $column) {
            $name = $column->getName();
            if ($name === 'name' || ends_with($name, '_name') || ends_with($name, '_code')) {
                $variables['keywordColumns'][] = $name;
            }
        }

        return $variables;
    }
}
