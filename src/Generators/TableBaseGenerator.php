<?php
namespace LaravelRocket\Generator\Generators;

use LaravelRocket\Generator\Objects\Column;
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
     * @var \LaravelRocket\Generator\Objects\Definitions
     */
    protected $json;

    /** @var \LaravelRocket\Generator\Objects\Table */
    protected $tableObject;

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
        $this->fileService->render($view, $path, $variables);

        return true;
    }

    /**
     * @param Table   $table
     * @param Table[] $tables
     */
    public function setTargetTable($table, $tables)
    {
        $this->table       = $table;
        $this->tables      = $tables;
        $this->tableObject = new \LaravelRocket\Generator\Objects\Table($this->table, $this->tables, $this->json);
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
        return $this->tableObject->getModelName();
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
     * @return \LaravelRocket\Generator\Objects\Relation[]
     */
    public function getRelations(): array
    {
        return $this->tableObject->getRelations();
    }

    /**
     * @return array
     */
    protected function getColumns()
    {
        $columnInfo = [
            'editableColumns' => [],
            'listColumns'     => [],
            'showableColumns' => [],
        ];

        $relations    = $this->getRelations();
        $relationHash = [];
        foreach ($relations as $relation) {
            if ($relation->getType() === 'belongsTo') {
                $relationHash[$relation->getReferenceColumn()->getName()] = $relation;
            }
        }

        foreach ($this->table->getColumns() as $column) {
            $name             = $column->getName();
            $relation         = '';
            $columnDefinition = $this->json->getColumnDefinition($this->table->getName(), $column->getName());

            $columnObject = new Column($column);

            $type    = $columnObject->getEditFieldType();
            $options = $columnObject->getEditFieldOptions();

            $this->copyTypeRelatedFiles($type);

            if (array_key_exists($name, $relationHash)) {
                $relation = camel_case($relationHash[$name]->getName());
            }

            if ($columnObject->isListable()) {
                $columnInfo['listColumns'][$name] = [
                    'name'     => $name,
                    'type'     => $type,
                    'relation' => $relation,
                    'options'  => $options,
                ];
            }

            if ($columnObject->isEditable()) {
                $columnInfo['editableColumns'][$name] = [
                    'name'     => $name,
                    'type'     => $type,
                    'relation' => $relation,
                    'options'  => $options,
                ];
            }

            if ($columnObject->isShowable()) {
                $columnInfo['showableColumns'][$name] = [
                    'name'     => $name,
                    'type'     => $type,
                    'relation' => $relation,
                    'options'  => $options,
                ];
            }
        }

        $relationDefinitions = $this->json->get(['tables', $this->table->getName(), 'relations'], []);
        foreach ($relationDefinitions as $name => $relationDefinition) {
            if (array_key_exists($name, $columnInfo['editableColumns'])) {
                $columnInfo['editableColumns']['name']['type'] = array_get($relationDefinitions, 'type', '');
            } else {
                $columnInfo['editableColumns'][$name] = [
                    'name' => $name,
                    'type' => array_get($relationDefinitions, 'type', ''),
                ];
            }
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

    protected function detectRepresentativeColumn()
    {
        foreach ($this->table->getColumns() as $column) {
            $name = $column->getName();
            if ($name === 'name') {
                return $column->getName();
            }
        }

        foreach ($this->table->getColumns() as $column) {
            $name = $column->getName();
            if (ends_with($name, '_name')) {
                return $column->getName();
            }
            if ($name === 'title') {
                return $column->getName();
            }
        }

        return 'id';
    }
}
