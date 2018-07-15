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
        $modelName                    = $this->getModelName();
        $variables                    = [];
        $variables['modelName']       = $modelName;
        $variables['className']       = $modelName.'Repository';
        $variables['variableName']    = camel_case($modelName);
        $variables['tableName']       = $this->table->getName();
        $variables['relationTable']   = $this->detectRelationTable($this->table);
        $variables['relations']       = $this->getRelations();
        $variables['baseClass']       = $variables['relationTable'] ? 'RelationModelRepository' : 'SingleKeyModelRepository';
        $variables['keywordColumns']  = [];
        $variables['existingMethods'] = $this->getExistingMethods();

        if ($variables['relationTable']) {
            $keys                   = $this->getRelationKey($this->table);
            $variables              = array_merge($variables, $keys);
            $variables['parentKey'] = array_get($keys, 'parentKey', '');
            $variables['childKey']  = array_get($keys, 'childKey', '');
        }

        $targetColumns   = ['name', 'title', 'content', 'note', 'description'];
        $targetPostfixes = ['_name', '_code'];
        foreach ($this->table->getColumns() as $column) {
            $name = $column->getName();
            if (in_array($name, $targetColumns) || ends_with($name, $targetPostfixes)) {
                $variables['keywordColumns'][] = $name;
            }
        }

        return $variables;
    }
}
