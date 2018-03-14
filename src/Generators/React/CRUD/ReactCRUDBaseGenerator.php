<?php
namespace LaravelRocket\Generator\Generators\React\CRUD;

use LaravelRocket\Generator\Generators\TableBaseGenerator;
use LaravelRocket\Generator\Objects\Table;

class ReactCRUDBaseGenerator extends TableBaseGenerator
{
    protected function canGenerate(): bool
    {
        foreach ($this->excludePostfixes as $excludePostfix) {
            if (ends_with($this->table->getName(), $excludePostfix)) {
                return false;
            }
        }

        if (!$this->rebuild) {
            $path = $this->getPath();
            if (!empty($path) && file_exists($path)) {
                return false;
            }
        }

        return !$this->detectRelationTable($this->table);
    }

    /**
     * @return array
     */
    protected function getVariables(): array
    {
        $modelName   = $this->getModelName();
        $tableObject = new Table($this->table, $this->tables);

        $variables = [
            'table'        => $tableObject,
            'modelName'    => $modelName,
            'pathName'     => $tableObject->getPathName(),
            'variableName' => lcfirst($modelName),
            'className'    => $this->getClassName(),
            'title'        => $tableObject->getDisplayName(),
        ];

        return array_merge($variables, $tableObject->getTestColumn());
    }

    protected function getClassName(): string
    {
        return $this->getModelName();
    }
}
