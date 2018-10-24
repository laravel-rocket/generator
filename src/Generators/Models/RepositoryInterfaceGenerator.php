<?php
namespace LaravelRocket\Generator\Generators\Models;

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
     * @return string
     */
    protected function getBaseClass(): string
    {
        $relationTable = $this->detectRelationTable($this->table);
        if (!empty($relationTable)) {
            return 'RelationModelRepository';
        }

        if ($this->hasAuthenticationModel()) {
            return 'AuthenticatableRepository';
        }

        return 'SingleKeyModelRepository';
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
        $variables['baseClass']       = $this->getBaseClass();
        $variables['existingMethods'] = $this->getExistingMethods();

        return $variables;
    }
}
