<?php
namespace LaravelRocket\Generator\Generators;

use TakaakiMizuno\MWBParser\Elements\Table;
use function ICanBoogie\singularize;

class TableBaseGenerator extends BaseGenerator
{
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
        $this->table  = $table;
        $this->tables = $tables;

        if (!$this->canGenerate()) {
            return false;
        }

        $path = $this->getPath();
        if (file_exists($path)) {
            unlink($path);
        }
        $view      = $this->getView();
        $variables = $this->getVariables();

        $this->fileService->render($view, $path, $variables, true);

        return true;
    }

    /**
     * @return bool
     */
    protected function canGenerate(): bool
    {
        return true;
    }

    /**
     * @return string
     */
    protected function getModelName(): string
    {
        return title_case(singularize($this->table->getName()));
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
    protected function getRelations(): array
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

            $column          = $columns[0];
            $referenceColumn = $referenceColumns[0];
            $relations[]     = [
                'type'            => 'belongsTo',
                'column'          => $column,
                'referenceColumn' => $referenceColumn,
                'referenceTable'  => $foreignKey->getReferenceTableName(),
                'name'            => camel_case(singularize($foreignKey->getReferenceTableName())),
                'referenceModel'  => title_case(singularize($foreignKey->getReferenceTableName())),
            ];
        }
        foreach ($this->tables as $table) {
            if ($this->table->getName() === $table->getName()) {
                continue;
            }
            $relationTableName    = '';
            $relationTableColumns = ['', ''];

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
                if ($table->getName() === $foreignKey->getReferenceTableName()) {
                    $relations[]             = [
                        'type'            => $foreignKey->hasMany() ? 'hasMany' : 'hasOne',
                        'column'          => $column,
                        'referenceColumn' => $referenceColumn,
                        'referenceTable'  => $foreignKey->getReferenceTableName(),
                        'name'            => $foreignKey->hasMany() ? camel_case($foreignKey->getReferenceTableName()) : camel_case(singularize($foreignKey->getReferenceTableName())),
                        'referenceModel'  => title_case(singularize($foreignKey->getReferenceTableName())),
                    ];
                    $relationTableColumns[0] = $referenceColumn;
                    $hasRelation             = true;
                } else {
                    $relationTableName       = $table->getName();
                    $relationTableColumns[1] = $referenceColumn;
                }
            }

            if ($hasRelation && $this->detectRelationTable($table)) {
                $relations[] = [
                    'type'            => 'belongsToMany',
                    'relationTable'   => $table->getName(),
                    'column'          => $relationTableColumns[0],
                    'referenceColumn' => $relationTableColumns[1],
                    'referenceTable'  => $relationTableName,
                    'name'            => camel_case(singularize($relationTableName)),
                    'referenceModel'  => title_case(singularize($relationTableName)),
                ];
            }
        }

        return $relations;
    }
}
