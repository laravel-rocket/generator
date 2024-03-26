<?php

namespace LaravelRocket\Generator\Generators\APIs\Admin;

use Illuminate\Support\Str;

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

        $className = ucfirst(Str::camel($action)).'Request';

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

        $this->setTargetTable($table, $tables);

        if (!$this->canGenerate()) {
            return false;
        }

        $variables = $this->getVariables();

        foreach (['index', 'show', 'store', 'update'] as $action) {
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
            $actionVariables = array_merge($variables, $this->getActionVariables($action));

            $this->fileService->render($view, $path, $actionVariables);
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

        $variables = [
            'className' => ucfirst(Str::camel($action)).'Request',
            'baseClass' => $action === 'index' ? 'PaginationRequest' : 'Request',
        ];

        return $variables;
    }
}
