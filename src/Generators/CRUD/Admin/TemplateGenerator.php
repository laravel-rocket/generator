<?php
namespace LaravelRocket\Generator\Generators\CRUD\Admin;

use LaravelRocket\Generator\Generators\CRUD\TemplateGenerator as BaseTemplateGenerator;
use function ICanBoogie\pluralize;

class TemplateGenerator extends BaseTemplateGenerator
{
    /**
     * @param string $action
     *
     * @return string
     */
    protected function getActionPath(string $action): string
    {
        $modelName = $this->getModelName();
        $viewName  = pluralize(kebab_case($modelName));

        return resource_path('views/pages/'.$viewName.'/'.$action.'.blade.php');
    }

    /**
     * @param string $action
     *
     * @return string
     */
    protected function getActionView(string $action): string
    {
        return 'crud.admin.views.'.$action;
    }
}
