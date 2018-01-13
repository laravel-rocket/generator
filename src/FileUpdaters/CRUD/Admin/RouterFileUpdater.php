<?php
namespace LaravelRocket\Generator\FileUpdaters\CRUD\Admin;

use LaravelRocket\Generator\FileUpdaters\CRUD\RouterFileUpdater as BaseRouterFileUpdater;

class RouterFileUpdater extends BaseRouterFileUpdater
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
            if (strpos($line, '\'admin.auth\'') !== false) {
                $start = true;
            }
            if (strpos($line, '});') !== false && $start === true) {
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
        Route::resource('$viewName', 'Admin\{$modelName}Controller');
EOS;
    }
}
