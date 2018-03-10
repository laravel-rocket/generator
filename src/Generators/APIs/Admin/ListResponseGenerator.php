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

        return app_path('Http/Responses/Api/Admin/'.pluralize($modelName).'.php');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return 'api.admin.list_response';
    }

    /**
     * @return string
     */
    protected function getClassName(): string
    {
        return pluralize($this->getModelName());
    }
}
