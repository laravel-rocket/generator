<?php
namespace LaravelRocket\Generator\Generators\CRUD;

use function ICanBoogie\pluralize;

class TemplateGenerator extends CRUDBaseGenerator
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
        $this->table  = $table;
        $this->tables = $tables;

        if (!$this->canGenerate()) {
            return false;
        }

        $variables = $this->getVariables();

        foreach (['index', 'edit'] as $action) {
            $path = $this->getActionPath($action);
            if (file_exists($path)) {
                unlink($path);
            }
            $view = $this->getActionView($action);

            if (file_exists($path)) {
                unlink($path);
            }

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
        return '';
    }

    /**
     * @param string $action
     *
     * @return string
     */
    protected function getActionView(string $action): string
    {
        return '';
    }

    /**
     * @return array
     */
    protected function getVariables(): array
    {
        $modelName                 = $this->getModelName();
        $variables                 = $this->getColumns();
        $variables['modelName']    = $modelName;
        $variables['variableName'] = camel_case($modelName);
        $variables['viewName']     = kebab_case(pluralize($modelName));
        $variables['className']    = $modelName.'Repository';
        $variables['tableName']    = $this->table->getName();

        return $variables;
    }
}
