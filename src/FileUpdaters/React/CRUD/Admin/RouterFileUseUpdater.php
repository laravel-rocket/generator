<?php
namespace LaravelRocket\Generator\FileUpdaters\React\CRUD\Admin;

use Illuminate\Support\Str;
use LaravelRocket\Generator\FileUpdaters\TableBaseFileUpdater;
use function ICanBoogie\pluralize;

class RouterFileUseUpdater extends TableBaseFileUpdater
{
    public function needGenerate()
    {
        foreach ($this->excludePostfixes as $excludePostfix) {
            if (Str::endsWith($this->table->getName(), $excludePostfix)) {
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
            if (strpos($line, 'class App') !== false) {
                return $index;
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
            if (strpos($line, 'import '.$modelName.'Index') !== false) {
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
        $pathName    = pluralize($modelName);

        return <<< EOS
import {$modelName}Index from '../../views/$pathName/{$modelName}Index';
import {$modelName}Show from '../../views/$pathName/{$modelName}Show';
import {$modelName}Edit from "../../views/$pathName/{$modelName}Edit";

EOS;
    }
}
