<?php

namespace LaravelRocket\Generator\Generators\React\CRUD;

use Illuminate\Support\Str;
use LaravelRocket\Generator\Generators\TableBaseGenerator;
use LaravelRocket\Generator\Objects\Table;

class ReactCRUDBaseGenerator extends TableBaseGenerator
{
    protected $excludePostfixes = ['password_resets', 'roles'];

    protected function canGenerate(): bool
    {
        foreach ($this->excludePostfixes as $excludePostfix) {
            if (Str::endsWith($this->table->getName(), $excludePostfix)) {
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
        $tableObject = new Table($this->table, $this->tables, $this->json);

        $relations = [];
        foreach ($tableObject->getColumns() as $column) {
            if ($column->isAPIReturnable()) {
                $relation = $column->getRelation();
                if (!empty($relation)) {
                    $referenceTableObject            = new Table(
                        $this->findTableFromName($relation->getReferenceTableName()),
                        $this->tables,
                        $this->json
                    );
                    $relations[$relation->getName()] = $referenceTableObject->getModelName();
                }
            }
        }

        $variables = [
            'table'        => $tableObject,
            'modelName'    => $modelName,
            'pathName'     => $tableObject->getPathName(),
            'variableName' => lcfirst($modelName),
            'className'    => $this->getClassName(),
            'title'        => $tableObject->getDisplayName(),
            'relations'    => $relations,
        ];

        return array_merge($variables, $tableObject->getTestColumn());
    }

    protected function getClassName(): string
    {
        return $this->getModelName();
    }
}
