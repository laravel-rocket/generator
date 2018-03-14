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
            'columns' => [],
            'list'    => [],
            'show'    => [],
            'edit'    => [],
        ];

        foreach ($tableObject->getColumns() as $column) {
            if ($column->isAPIReturnable()) {
                $result['columns'][$column->getName()] = [
                    'name'     => $column->getDisplayName(),
                    'type'     => $column->getEditFieldType(),
                    'editable' => $column->isEditable(),
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
            }
        }

        $variables['columnInfo'] = $result;

        return $variables;
    }
}
