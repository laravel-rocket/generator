<?php
namespace LaravelRocket\Generator\FileUpdaters\APIs\Admin;

use LaravelRocket\Generator\FileUpdaters\TableBaseFileUpdater;

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
        return base_path('routes/api/admin.php');
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

        $start = false;

        foreach ($lines as $index => $line) {
            if (strpos($line, '\'admin.auth\'') !== false) {
                $start = true;
            }
            if (strpos($line, '});') !== false && $start === true) {
                return $index + 1;
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
            if (strpos($line, "'".$modelName.'Controller') !== false) {
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
        $modelName = $this->getModelName();
        $viewName  = kebab_case($this->table->getName());

        return <<< EOS
            Route::resource('$viewName', '{$modelName}Controller')->only([
                'index', 'show', 'store', 'update', 'destroy',
            ]);
EOS;
    }
}
