<?php

namespace LaravelRocket\Generator\Generators\Models;

class RelationLanguageFileGenerator extends ModelBaseGenerator
{
    /**
     * @return string
     */
    protected function getPath(): string
    {
        return resource_path('lang/en/tables/'.$this->table->getName().'/relations.php');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return 'model.relation_language';
    }

    /**
     * @return array
     */
    protected function getVariables(): array
    {
        $modelName              = $this->getModelName();
        $variables              = [
            'relations' => $this->getRelations(),
        ];
        $variables['modelName'] = $modelName;
        $variables['tableName'] = $this->table->getName();

        return $variables;
    }
}
