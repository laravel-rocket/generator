<?php
namespace LaravelRocket\Generator\Generators\APIs\Admin;

class RequestGenerator extends BaseAdminAPIGenerator
{
    /**
     * @param string $action
     *
     * @return string
     */
    protected function getActionPath(string $action): string
    {
        $modelName = $this->getModelName();
        $nameSpace = ucfirst($modelName);

        $className = ucfirst(camel_case($action)).'Request';

        return app_path('Http/Requests/Api/Admin/'.$nameSpace.'/'.$className.'.php');
    }

    /**
     * @param string $action
     *
     * @return string
     */
    protected function getActionView(string $action): string
    {
        return 'api.admin.request';
    }

    public function generate($table, $tables, $json): bool
    {
        $this->json = $json;

        $this->table  = $table;
        $this->tables = $tables;

        if (!$this->canGenerate()) {
            return false;
        }

        $variables = $this->getVariables();

        foreach (['index', 'show', 'store', 'update', 'destroy'] as $action) {
            $path = $this->getActionPath($action);
            if (file_exists($path)) {
                if (!$this->rebuild) {
                    continue;
                }

                unlink($path);
            }
            $view = $this->getActionView($action);

            if (file_exists($path)) {
                unlink($path);
            }
            $variables = array_merge($variables, $this->getActionVariables($action));

            $this->fileService->render($view, $path, $variables, false, true);
        }

        return true;
    }

    /**
     * @param string $action
     *
     * @return array
     */
    protected function getActionVariables(string $action): array
    {
        $modelName = $this->getModelName();
        $nameSpace = ucfirst($modelName);

        $variables = [
            'className' => ucfirst(camel_case($action)).'Request',
            'baseClass' => $action === 'index' ? 'PaginationRequest' : 'Request',
            'nameSpace' => $nameSpace,
        ];

        return $variables;
    }
}
