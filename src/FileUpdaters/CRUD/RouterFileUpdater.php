<?php
namespace LaravelRocket\Generator\FileUpdaters\CRUD;

use LaravelRocket\Generator\FileUpdaters\TableBaseFileUpdater;

class RouterFileUpdater extends TableBaseFileUpdater
{
    protected function getTargetFilePath(): string
    {
        return app_path('routers/admin.php');
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
            if (strpos($line, '\'user.auth\'') !== false) {
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
            if (strpos($line, $modelName.'Controller') !== false) {
                return $index;
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
        Route::resource('$viewName', 'User\{$modelName}Controller');
EOS;
    }
}
