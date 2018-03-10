<?php
namespace LaravelRocket\Generator\Generators\APIs\Admin;

use function ICanBoogie\pluralize;

class ListResponseGenerator extends BaseAdminAPIGenerator
{
    /**
     * @return string
     */
    protected function getPath(): string
    {
        $modelName = $this->getModelName();

        return app_path('Http/Response/Api/Admin/'.pluralize($modelName).'.php');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return 'api.admin.list_response';
    }

    /**
     * @return array
     */
    protected function getVariables(): array
    {
        $variables = parent::getVariables();

        $modelName              = $this->getModelName();
        $variables['className'] = pluralize($modelName);

        return $variables;
    }
}
