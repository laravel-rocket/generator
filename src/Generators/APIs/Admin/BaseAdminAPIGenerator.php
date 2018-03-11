<?php
namespace LaravelRocket\Generator\Generators\APIs\Admin;

use LaravelRocket\Generator\Generators\TableBaseGenerator;
use LaravelRocket\Generator\Objects\Table;

class BaseAdminAPIGenerator extends TableBaseGenerator
{
    protected function canGenerate(): bool
    {
        $result = parent::canGenerate();
        if ($result) {
            return !$this->detectRelationTable($this->table);
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function getVariables(): array
    {
        $modelName   = $this->getModelName();
        $tableObject = new Table($this->table, $this->tables);

        $variables = [
            'table'            => $tableObject,
            'modelName'        => $modelName,
            'variableName'     => lcfirst($modelName),
            'className'        => $this->getClassName(),
            'requestNameSpace' => $modelName,
        ];

        return array_merge($variables, $tableObject->getTestColumn());
    }

    protected function getClassName(): string
    {
        return $this->getModelName();
    }
}
