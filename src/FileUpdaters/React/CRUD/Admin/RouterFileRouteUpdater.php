<?php
namespace LaravelRocket\Generator\FileUpdaters\React\CRUD\Admin;

use function ICanBoogie\pluralize;
use Illuminate\Support\Str;
use LaravelRocket\Generator\Objects\Table;

class RouterFileRouteUpdater extends ReactCRUDAdminFileUpdater
{
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
        $pathName    = Str::snake(pluralize($modelName));
        $tableObject = new Table($this->table, $this->tables, $this->json);
        $displayName = $tableObject->getDisplayName();

        return <<< EOS
                  <PropsRoute path="/$pathName/:id/edit" name="$displayName Edit" component={{$modelName}Edit} {...this.state}/>
                  <PropsRoute path="/$pathName/create" name="$displayName Create" component={{$modelName}Edit} {...this.state}/>
                  <PropsRoute path="/$pathName/:id" name="$displayName Show" component={{$modelName}Show} {...this.state}/>
                  <PropsRoute path="/$pathName" name="$displayName" component={{$modelName}Index} {...this.state}/>

EOS;
    }
}
