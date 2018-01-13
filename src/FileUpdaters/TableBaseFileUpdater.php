<?php
namespace LaravelRocket\Generator\FileUpdaters;

use function ICanBoogie\singularize;

class TableBaseFileUpdater extends BaseFileUpdater
{
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
        return true;
    }

    /**
     * @return string
     */
    protected function getModelName(): string
    {
        return ucfirst(camel_case(singularize($this->table->getName())));
    }
}
