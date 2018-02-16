<?php
namespace LaravelRocket\Generator\Generators;

use LaravelRocket\Generator\Objects\Column;
use TakaakiMizuno\MWBParser\Elements\Table;
use function ICanBoogie\pluralize;
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
     * @var \LaravelRocket\Generator\Objects\Definitions
     */
    protected $json;

    /**
     * @param Table                                        $table
     * @param Table[]                                      $tables
     * @param \LaravelRocket\Generator\Objects\Definitions $json
     *
     * @return bool
     */
    public function generate($table, $tables, $json): bool
    {
        $this->json = $json;

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
        $names     = [];

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

            if (in_array($relationName, $names)) {
                continue;
            }

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
            $names[] = $relationName;
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

                $relationName = pluralize(preg_replace('/_id$/', '', $columns[0]->getName()));
                if ($foreignKey->getReferenceTableName() === $relationName) {
                    $relationName = $table->getName();
                }
                $relationName = $foreignKey->hasMany() ? pluralize(camel_case($relationName)) : singularize(camel_case($relationName));

                if ($this->table->getName() === $foreignKey->getReferenceTableName() && !in_array($relationName, $names)) {
                    $relations[]             = [
                        'type'            => $foreignKey->hasMany() ? 'hasMany' : 'hasOne',
                        'column'          => $referenceColumn,
                        'referenceColumn' => $column,
                        'referenceTable'  => $table->getName(),
                        'name'            => $relationName,
                        'referenceModel'  => ucfirst(camel_case(singularize($table->getName()))),
                    ];
                    $relationTableColumns[0] = $column;
                    $relationTableNames[0]   = $foreignKey->getReferenceTableName();
                    $hasRelation             = true;
                    $names[]                 = $relationName;
                } else {
                    $relationTableName       = $table->getName();
                    $relationTableColumns[1] = $column;
                    $relationTableNames[1]   = $foreignKey->getReferenceTableName();
                }
            }

            $relationName = camel_case($relationTableNames[1]);

            if ($hasRelation && $this->detectRelationTable($table) && !in_array($relationName, $names)) {
                $relations[] = [
                    'type'            => 'belongsToMany',
                    'relationTable'   => $table->getName(),
                    'column'          => $relationTableColumns[0],
                    'referenceColumn' => $relationTableColumns[1],
                    'referenceTable'  => $relationTableNames[1],
                    'name'            => $relationName,
                    'referenceModel'  => ucfirst(camel_case(singularize($relationTableName))),
                ];
                $names[] = $relationName;
            }
        }

        return $relations;
    }

    protected function getColumns()
    {
        $columnInfo = [
            'editableColumns' => [],
            'listColumns'     => [],
        ];

        $relations    = $this->getRelations();
        $relationHash = [];
        foreach ($relations as $relation) {
            if ($relation['type'] === 'belongsTo') {
                $relationHash[$relation['referenceColumn']->getName()] = $relation;
            }
        }

        foreach ($this->table->getColumns() as $column) {
            $name             = $column->getName();
            $relation         = '';
            $columnDefinition = $this->json->getColumnDefinition($this->table->getName(), $column->getName());

            $columnObject = new Column($column);

            list($type, $options) = $columnObject->getEditFieldType($relationHash, $columnDefinition);
            $this->copyTypeRelatedFiles($type);

            if (array_key_exists($name, $relationHash)) {
                $relation = camel_case($relationHash[$name]['name']);
            }

            if ($columnObject->isListable()) {
                $columnInfo['listColumns'][] = [
                    'name'     => $name,
                    'type'     => $type,
                    'relation' => $relation,
                    'options'  => $options,
                ];
            }

            if ($columnObject->isEditable()) {
                $columnInfo['editableColumns'][] = [
                    'name'     => $name,
                    'type'     => $type,
                    'relation' => $relation,
                    'options'  => $options,
                ];
            }
        }

        $relationDefinitions = $this->json->get(['tables', $this->table->getName(), 'relations'], []);
        foreach ($relationDefinitions as $name => $relationDefinition) {
            $columnInfo['editableColumns'][] = [
                'name'     => $name,
                'type'     => $type,
                'relation' => $relation,
                'options'  => $options,
            ];
        }

        return $columnInfo;
    }

    protected function generateConstantName($column, $value)
    {
        return strtoupper(implode('_', [$column, $value]));
    }

    protected function copyTypeRelatedFiles($type)
    {
        switch ($type) {
            case 'country':
                $this->copyConfigFile(['data', 'data', 'countries.php']);
                $this->copyConfigFile(['data', 'data', 'phones.php']);
                $this->copyLanguageFile(['data', 'countries.php']);
                break;
            case 'currency':
                $this->copyConfigFile(['data', 'data', 'currencies.php']);
                $this->copyLanguageFile(['data', 'currencies.php']);
        }
    }
}
