<?php

namespace LaravelRocket\Generator\Generators\React\CRUD\Admin;

use Illuminate\Support\Arr;
use LaravelRocket\Generator\Generators\React\CRUD\ReactCRUDBaseGenerator;
use LaravelRocket\Generator\Objects\Table;

use function ICanBoogie\pluralize;

class ColumnGenerator extends ReactCRUDBaseGenerator
{
    /**
     * @return string
     */
    protected function getPath(): string
    {
        $modelName = $this->getModelName();

        return resource_path('assets/admin/src/views/'.pluralize($modelName).'/_columns.js');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return 'react.crud.admin._columns';
    }

    /**
     * @return array
     */
    protected function getVariables(): array
    {
        $variables = parent::getVariables();

        /** @var Table $tableObject */
        $tableObject = $variables['table'];

        $result = [
            'columns'   => [],
            'list'      => [],
            'show'      => [],
            'edit'      => [],
            'relations' => $variables['relations'],
        ];

        $crudListColumns = Arr::get($this->json->getTableCRUDDefinition($tableObject->getName(), 'list'), 'columns', []);
        $crudShowColumns = Arr::get($this->json->getTableCRUDDefinition($tableObject->getName(), 'show'), 'columns', []);
        $crudEditColumns = Arr::get($this->json->getTableCRUDDefinition($tableObject->getName(), 'edit'), 'columns', []);

        foreach ($tableObject->getColumns() as $column) {
            $options     = [];
            $optionNames = [];
            foreach ($column->getEditFieldOptions() as $option) {
                $optionValue               = Arr::get($option, 'value', 'unknown');
                $optionName                = Arr::get($option, 'name', 'Unknown');
                $options[]                 = $optionValue;
                $optionNames[$optionValue] = $optionName;
            }

            $result['columns'][$column->getKeyName()] = [
                'name'         => $column->getDisplayName(),
                'type'         => $column->getEditFieldType(),
                'editable'     => $column->isEditable(),
                'queryName'    => $column->getQueryName(),
                'apiName'      => $column->getAPIName(),
                'presentation' => $column->getPresentation(),
                'options'      => $options,
                'optionNames'  => $optionNames,
            ];
            if ((count($crudListColumns) === 0 && $column->isListable()) || (count($crudListColumns) > 0 && in_array($column->getKeyName(), $crudListColumns))) {
                $result['list'][] = $column->getKeyName();
            }
            if ((count($crudShowColumns) === 0 && $column->isShowable()) || (count($crudShowColumns) > 0 && in_array($column->getKeyName(), $crudShowColumns))) {
                $result['show'][] = $column->getKeyName();
            }
            if ((count($crudEditColumns) === 0 && $column->isEditable()) || (count($crudEditColumns) > 0 && in_array($column->getKeyName(), $crudEditColumns))) {
                $result['edit'][] = $column->getKeyName();
            }
            $relation = $column->getRelation();
            if (!empty($relation)) {
                $result['columns'][$column->getKeyName()]['relation'] = $relation->getName();
            }
        }

        foreach ($tableObject->getRelations() as $relation) {
            if ($relation->shouldIncludeInAPI()) {
                $options     = [];
                $optionNames = [];
                foreach ($relation->getInterestedColumnOptions() as $option) {
                    $optionValue               = Arr::get($option, 'value', 'unknown');
                    $optionName                = Arr::get($option, 'name', 'Unknown');
                    $options[]                 = $optionValue;
                    $optionNames[$optionValue] = $optionName;
                }
                $referenceTableObject = new Table($this->findTableFromName($relation->getReferenceTableName()), $this->tables, $this->json);

                $result['columns'][$relation->getName()] = [
                    'name'         => $relation->getDisplayName(),
                    'type'         => $relation->getEditFieldType(),
                    'editable'     => $relation->isEditable(),
                    'queryName'    => $relation->getQueryName(),
                    'apiName'      => $relation->getAPIName(),
                    'options'      => $options,
                    'optionNames'  => $optionNames,
                    'link'         => '/'.$referenceTableObject->getPathName(),
                    'presentation' => $relation->getPresentation(),
                ];
                if ($relation->isListable() || (count($crudListColumns) > 0 && in_array($relation->getName(), $crudListColumns))) {
                    $result['list'][] = $relation->getName();
                }
                if ($relation->isShowable() || (count($crudShowColumns) > 0 && in_array($relation->getName(), $crudShowColumns))) {
                    $result['show'][] = $relation->getName();
                }
                if ($relation->isEditable() || (count($crudEditColumns) > 0 && in_array($relation->getName(), $crudEditColumns))) {
                    $result['edit'][] = $relation->getName();
                }
            }
        }

        $variables['columnInfo'] = $result;

        return $variables;
    }
}
