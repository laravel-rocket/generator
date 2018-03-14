<?php
namespace LaravelRocket\Generator\Generators\React\CRUD\Admin;

use LaravelRocket\Generator\Generators\React\CRUD\ReactCRUDBaseGenerator;
use function ICanBoogie\pluralize;

class ViewGenerator extends ReactCRUDBaseGenerator
{
    /**
     * @param \TakaakiMizuno\MWBParser\Elements\Table      $table
     * @param \TakaakiMizuno\MWBParser\Elements\Table[]    $tables
     * @param \LaravelRocket\Generator\Objects\Definitions $json
     *
     * @return bool
     */
    public function generate($table, $tables, $json): bool
    {
        $this->json = $json;

        $this->table  = $table;
        $this->tables = $tables;

        if (!$this->canGenerate()) {
            return false;
        }

        $variables = $this->getVariables();

        foreach (['index', 'show', 'edit'] as $action) {
            $path = $this->getActionPath($action);
            if (file_exists($path)) {
                if (!$this->rebuild) {
                    continue;
                }

                unlink($path);
            }
            $view = $this->getActionView($action);

            $this->fileService->render($view, $path, $variables, false, true);
        }

        return true;
    }

    /**
     * @param string $action
     *
     * @return string
     */
    protected function getActionPath(string $action): string
    {
        $modelName = $this->getModelName();
        $viewName  = $modelName.ucfirst($action).'.js';

        return resource_path('assets/admin/src/views/'.pluralize($modelName).'/'.$viewName);
    }

    /**
     * @param string $action
     *
     * @return string
     */
    protected function getActionView(string $action): string
    {
        return 'react.crud.admin.views.'.$action;
    }
}
