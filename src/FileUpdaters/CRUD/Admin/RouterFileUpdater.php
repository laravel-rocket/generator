<?php
namespace LaravelRocket\Generator\FileUpdaters\CRUD\Admin;

use LaravelRocket\Generator\FileUpdaters\CRUD\RouterFileUpdater as BaseRouterFileUpdater;

class RouterFileUpdater extends BaseRouterFileUpdater
{
    public function needGenerate()
    {
        return !$this->detectRelationTable($this->table);
    }

    protected function getTargetFilePath(): string
    {
        return base_path('routes/admin.php');
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
     * @return string
     */
    protected function getInsertData(): string
    {
        $controllerName = $this->getModelName().'Controller';
        $viewName       = kebab_case(camel_case($this->table->getName()));

        return <<< EOS
        Route::resource('$viewName', 'Admin\\$controllerName');

EOS;
    }
}
