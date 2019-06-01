<?php
namespace LaravelRocket\Generator\FileUpdaters;

use Illuminate\Support\Str;
use LaravelRocket\Generator\Objects\Table;
use function ICanBoogie\singularize;

class TableBaseFileUpdater extends BaseFileUpdater
{
    protected $excludePostfixes = ['password_resets', 'roles'];

    /**
     * @var \TakaakiMizuno\MWBParser\Elements\Table
     */
    protected $table;

    /**
     * @var \TakaakiMizuno\MWBParser\Elements\Table[]
     */
    protected $tables;

    /**
     * @var Table
     */
    protected $tableObject;

    /**
     * @var \LaravelRocket\Generator\Objects\Definitions
     */
    protected $json;

    /**
     * @param \TakaakiMizuno\MWBParser\Elements\Table      $table
     * @param \TakaakiMizuno\MWBParser\Elements\Table[]    $tables
     * @param \LaravelRocket\Generator\Objects\Definitions $json
     *
     * @return bool
     */
    public function insert($table, $tables, $json): bool
    {
        $this->json = $json;

        $this->setTargetTable($table, $tables);

        if (!$this->needGenerate()) {
            return false;
        }

        $filePath = $this->getTargetFilePath();

        $existingPosition = $this->getExistingPosition();
        if ($existingPosition >= 0) {
            return false;
        }

        $insertPosition = $this->getInsertPosition();
        $data           = $this->getInsertData();

        return $this->insertDataToLine($data, $filePath, $insertPosition);
    }

    /**
     * @param \TakaakiMizuno\MWBParser\Elements\Table   $table
     * @param \TakaakiMizuno\MWBParser\Elements\Table[] $tables
     */
    public function setTargetTable($table, $tables)
    {
        $this->table       = $table;
        $this->tables      = $tables;
        $this->tableObject = new Table($table, $tables, $this->json);
    }

    public function needGenerate()
    {
        foreach ($this->excludePostfixes as $excludePostfix) {
            if (Str::endsWith($this->table->getName(), $excludePostfix)) {
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
        return ucfirst(Str::camel(singularize($this->table->getName())));
    }

    /**
     * @param \TakaakiMizuno\MWBParser\Elements\Table $table
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
}
