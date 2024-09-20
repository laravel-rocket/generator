<?php

namespace LaravelRocket\Generator\Generators\Models;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

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
        $variables['className']       = $modelName.'Repository';
        $variables['variableName']    = Str::camel($modelName);
        $variables['tableName']       = $this->table->getName();
        $variables['relationTable']   = $this->detectRelationTable($this->table);
        $variables['relations']       = $this->getRelations();
        $variables['baseClass']       = $this->getBaseClass();
        $variables['keywordColumns']  = [];
        $variables['existingMethods'] = $this->getExistingMethods();

        if ($variables['relationTable']) {
            $keys                   = $this->getRelationKey($this->table);
            $variables              = array_merge($variables, $keys);
            $variables['parentKey'] = Arr::get($keys, 'parentKey', '');
            $variables['childKey']  = Arr::get($keys, 'childKey', '');
        }

        $targetColumns   = ['name', 'title', 'content', 'note', 'description'];
        $targetPostfixes = ['_name', '_code'];
        foreach ($this->table->getColumns() as $column) {
            $name = $column->getName();
            if (in_array($name, $targetColumns) || Str::endsWith($name, $targetPostfixes)) {
                $variables['keywordColumns'][] = $name;
            }
        }

        return $variables;
    }
}
