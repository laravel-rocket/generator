<?php
namespace LaravelRocket\Generator\FileUpdaters\React\CRUD\Admin;

use LaravelRocket\Generator\FileUpdaters\TableBaseFileUpdater;
use LaravelRocket\Generator\Objects\Table;
use function ICanBoogie\pluralize;

class RouterFileUpdater extends TableBaseFileUpdater
{
    public function needGenerate()
    {
        foreach ($this->excludePostfixes as $excludePostfix) {
            if (ends_with($this->table->getName(), $excludePostfix)) {
                return false;
            }
        }

        return !$this->detectRelationTable($this->table);
    }

    protected function getTargetFilePath(): string
    {
        return resource_path('assets/admin/src/containers/App/App.js');
    }

    /**
     * @return int
     */
    protected function getInsertPosition(): int
    {
        $lines = file($this->getTargetFilePath());
        if ($lines === false) {
            return -1;
        }

        foreach ($lines as $index => $line) {
            if (strpos($line, '</Switch>') !== false) {
                return $index - 1;
            }
        }

        return -1;
    }

    /**
     * @return int
     */
    protected function getExistingPosition(): int
    {
        $modelName = $this->getModelName();

        $lines = file($this->getTargetFilePath());
        if ($lines === false) {
            return -1;
        }

        foreach ($lines as $index => $line) {
            if (strpos($line, '{'.$modelName.'Edit}') !== false) {
                return $index + 1;
            }
        }

        return -1;
    }

    /**
     * @return string
     */
    protected function getInsertData(): string
    {
        $modelName   = $this->getModelName();
        $pathName    = snake_case(pluralize($modelName));
        $tableObject = new Table($this->table, $this->tables);

        return <<< EOS
                  <PropsRoute path="/$pathName/:id/edit" name="$tableObject Edit" component={{$modelName}Edit} {...this.state}/>
                  <PropsRoute path="/$pathName/create" name="$tableObject Create" component={{$modelName}rEdit} {...this.state}/>
                  <PropsRoute path="/$pathName/:id" name="$tableObject Show" component={{$modelName}Show} {...this.state}/>
                  <PropsRoute path="/$pathName" name="$tableObject" component={{$modelName}Index} {...this.state}/>

EOS;
    }
}
