<?php
namespace LaravelRocket\Generator\FileUpdaters;

use function ICanBoogie\singularize;

class TableBaseFileUpdater extends BaseFileUpdater
{
    protected $excludePostfixes = ['password_resets'];

    /**
     * @var \TakaakiMizuno\MWBParser\Elements\Table
     */
    protected $table;

    /**
     * @var \TakaakiMizuno\MWBParser\Elements\Table[]
     */
    protected $tables;

    /**
     * @param \TakaakiMizuno\MWBParser\Elements\Table   $table
     * @param \TakaakiMizuno\MWBParser\Elements\Table[] $tables
     *
     * @return bool
     */
    public function insert($table, $tables): bool
    {
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
        $this->table  = $table;
        $this->tables = $tables;
    }

    public function needGenerate()
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
