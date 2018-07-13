<?php
namespace LaravelRocket\Generator\Generators\Models;

use LaravelRocket\Generator\Objects\Column;

class ModelGenerator extends ModelBaseGenerator
{
    /**
     * @return string
     */
    protected function getPath(): string
    {
        $modelName = $this->getModelName();

        return app_path('Models/'.$modelName.'.php');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return 'model.model';
    }

    /**
     * @return array
     */
    protected function getVariables(): array
    {
        $modelName                  = $this->getModelName();
        $variables                  = $this->getFillableColumns();
        $variables['className']     = $modelName;
        $variables['tableName']     = $this->table->getName();
        $variables['relationTable'] = $this->detectRelationTable($this->table);
        $variables['relations']     = $this->getRelations();
        $variables['constants']     = $this->getConstants();
        $variables['casts']         = $this->getCasts();
        $variables['traits']        = $this->getTraits();
        $variables['uses']          = $this->getUses();

        return $variables;
    }

    protected function getCasts(): array
    {
        $casts = [];
        foreach ($this->table->getColumns() as $column) {
            $columnDefinition = $this->json->getColumnDefinition($this->table->getName(), $column->getName());
            $columnObject     = new Column($column);

            $ediFieldType = $columnObject->getEditFieldType();
            $options      = $columnObject->getEditFieldOptions();

            $definitionType = array_get($columnDefinition, 'type', '');

            if ($ediFieldType === 'boolean' || $definitionType == 'boolean') {
                $casts[$column->getName()] = 'boolean';
            } elseif ($definitionType === 'array') {
                $casts[$column->getName()] = 'array';
            } elseif ($columnObject->isJson()) {
                $casts[$column->getName()] = 'array';
            }
        }

        return $casts;
    }

    protected function getFillableColumns()
    {
        $columnInfo = [
            'timestamps'      => [],
            'softDelete'      => false,
            'fillables'       => [],
            'authenticatable' => false,
        ];

        $excludes          = ['id', 'remember_token', 'created_at', 'deleted_at', 'updated_at'];
        $timestampExcludes = ['created_at', 'updated_at', 'deleted_at'];

        foreach ($this->table->getColumns() as $column) {
            $name = $column->getName();
            $type = $column->getType();

            if (!in_array($name, $excludes)) {
                $columnInfo['fillables'][] = $name;
            }
            if ($name == 'deleted_at') {
                $columnInfo['softDelete'] = true;
            }
            if ($name == 'remember_token') {
                $columnInfo['authenticatable'] = true;
            }
            if (($type == 'timestamp' || $type == 'timestamp_f') && !in_array($name, $timestampExcludes)) {
                $columnInfo['fillables'][] = $name;
            }
        }

        return $columnInfo;
    }
}
