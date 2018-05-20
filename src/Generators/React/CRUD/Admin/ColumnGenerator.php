<?php
namespace LaravelRocket\Generator\Generators\React\CRUD\Admin;

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
            'relations' => [],
        ];

        foreach ($tableObject->getColumns() as $column) {
            if ($column->isAPIReturnable()) {
                $result['columns'][$column->getName()] = [
                    'name'      => $column->getDisplayName(),
                    'type'      => $column->getEditFieldType(),
                    'editable'  => $column->isEditable(),
                    'queryName' => $column->getQueryName(),
                    'apiName'   => $column->getAPIName(),
                ];
                if ($column->isListable()) {
                    $result['list'][] = $column->getName();
                }
                if ($column->isShowable()) {
                    $result['show'][] = $column->getName();
                }
                if ($column->isEditable()) {
                    $result['edit'][] = $column->getName();
                }
                $relation = $column->getRelation();
                if (!empty($relation)) {
                    $result['columns'][$column->getName()]['relation'] = $relation->getName();
                    $referenceTableObject                              = new Table($this->findTableFromName($relation->getReferenceTableName()), $this->tables);
                    $result['relations'][$relation->getName()]         = $referenceTableObject->getModelName();
                }
            }
        }

        foreach ($tableObject->getRelations() as $relation) {
            if ($relation->shouldIncludeInAPI()) {
                $options              = [];
                $optionNames          = [];
                $referenceTableObject = new Table($this->findTableFromName($relation->getReferenceTableName()), $this->tables);

                $result['columns'][$relation->getName()] = [
                    'name'        => $relation->getDisplayName(),
                    'type'        => $relation->getEditFieldType(),
                    'editable'    => $relation->isEditable(),
                    'queryName'   => $relation->getQueryName(),
                    'apiName'     => $relation->getAPIName(),
                    'options'     => $options,
                    'optionNames' => $optionNames,
                    'link'        => '/'.$referenceTableObject->getPathName(),
                ];
                if ($relation->isListable()) {
                    $result['list'][] = $relation->getName();
                }
                if ($relation->isShowable()) {
                    $result['show'][] = $relation->getName();
                }
                if ($relation->isEditable()) {
                    $result['edit'][] = $relation->getName();
                }
            }
        }

        $variables['columnInfo'] = $result;

        return $variables;
    }
}
