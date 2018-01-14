<?php
namespace LaravelRocket\Generator\Generators;

use TakaakiMizuno\MWBParser\Elements\Table;
use function ICanBoogie\singularize;

class TableBaseGenerator extends BaseGenerator
{
    protected $excludePostfixes = ['password_resets'];

    /**
     * @var Table
     */
    protected $table;

    /**
     * @var Table[]
     */
    protected $tables;

    /**
     * @param Table   $table
     * @param Table[] $tables
     *
     * @return bool
     */
    public function generate($table, $tables): bool
    {
        $this->setTargetTable($table, $tables);

        if (!$this->canGenerate()) {
            return false;
        }

        $view      = $this->getView();
        $variables = $this->getVariables();

        $path = $this->getPath();
        if (file_exists($path)) {
            unlink($path);
        }
        $this->fileService->render($view, $path, $variables, true, true);

        return true;
    }

    /**
     * @param Table   $table
     * @param Table[] $tables
     */
    public function setTargetTable($table, $tables)
    {
        $this->table  = $table;
        $this->tables = $tables;
    }

    /**
     * @return bool
     */
    protected function canGenerate(): bool
    {
        foreach ($this->excludePostfixes as $excludePostfix) {
            if (ends_with($this->table->getName(), $excludePostfix)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return string
     */
    protected function getModelName(): string
    {
        return ucfirst(camel_case(singularize($this->table->getName())));
    }

    /**
     * @return string
     */
    protected function getPath(): string
    {
        return '';
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return '';
    }

    /**
     * @return array
     */
    protected function getVariables(): array
    {
        return [];
    }

    /**
     * @param Table $table
     *
     * @return bool
     */
    protected function detectRelationTable($table)
    {
        $foreignKeys = $table->getForeignKey();
        if (count($foreignKeys) != 2) {
            return false;
        }
        $tables = [];
        foreach ($foreignKeys as $foreignKey) {
            if (!$foreignKey->hasMany()) {
                return false;
            }
            $tables[] = $foreignKey->getReferenceTableName();
        }
        if ($table->getName() === implode('_', [singularize($tables[0]), $tables[1]]) || $table->getName() === implode('_', [singularize($tables[1]), $tables[0]])) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getRelations(): array
    {
        $relations = [];

        foreach ($this->table->getForeignKey() as $foreignKey) {
            $columns          = $foreignKey->getColumns();
            $referenceColumns = $foreignKey->getReferenceColumns();
            if (count($columns) == 0) {
                continue;
            }
            if (count($referenceColumns) == 0) {
                continue;
            }
            $relationName = camel_case(preg_replace('/_id$/', '', $columns[0]->getName()));

            $column          = $columns[0];
            $referenceColumn = $referenceColumns[0];
            $relations[]     = [
                'type'            => 'belongsTo',
                'column'          => $referenceColumn,
                'referenceColumn' => $column,
                'referenceTable'  => $foreignKey->getReferenceTableName(),
                'name'            => $relationName,
                'referenceModel'  => ucfirst(camel_case(singularize($foreignKey->getReferenceTableName()))),
            ];
        }
        foreach ($this->tables as $table) {
            if ($this->table->getName() === $table->getName()) {
                continue;
            }
            $relationTableName    = '';
            $relationTableColumns = ['', ''];
            $relationTableNames   = ['', ''];

            $hasRelation = false;

            foreach ($table->getForeignKey() as $foreignKey) {
                $columns          = $foreignKey->getColumns();
                $referenceColumns = $foreignKey->getReferenceColumns();
                if (count($columns) == 0) {
                    continue;
                }
                if (count($referenceColumns) == 0) {
                    continue;
                }
                $column          = $columns[0];
                $referenceColumn = $referenceColumns[0];
                if ($this->table->getName() === $foreignKey->getReferenceTableName()) {
                    $relations[]             = [
                        'type'            => $foreignKey->hasMany() ? 'hasMany' : 'hasOne',
                        'column'          => $referenceColumn,
                        'referenceColumn' => $column,
                        'referenceTable'  => $table->getName(),
                        'name'            => $foreignKey->hasMany() ? camel_case($table->getName()) : camel_case(singularize($table->getName())),
                        'referenceModel'  => ucfirst(camel_case(singularize($table->getName()))),
                    ];
                    $relationTableColumns[0] = $column;
                    $relationTableNames[0]   = $foreignKey->getReferenceTableName();
                    $hasRelation             = true;
                } else {
                    $relationTableName       = $table->getName();
                    $relationTableColumns[1] = $column;
                    $relationTableNames[1]   = $foreignKey->getReferenceTableName();
                }
            }

            if ($hasRelation && $this->detectRelationTable($table)) {
                $relations[] = [
                    'type'            => 'belongsToMany',
                    'relationTable'   => $table->getName(),
                    'column'          => $relationTableColumns[0],
                    'referenceColumn' => $relationTableColumns[1],
                    'referenceTable'  => $relationTableNames[1],
                    'name'            => camel_case($relationTableNames[1]),
                    'referenceModel'  => ucfirst(camel_case(singularize($relationTableName))),
                ];
            }
        }

        return $relations;
    }
}
