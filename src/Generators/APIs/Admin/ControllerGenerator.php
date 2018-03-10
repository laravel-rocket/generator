<?php
namespace LaravelRocket\Generator\Generators\APIs\Admin;

class ControllerGenerator extends BaseAdminAPIGenerator
{
    /**
     * @return string
     */
    protected function getPath(): string
    {
        $modelName = $this->getModelName();

        return app_path('Http/Controller/Api/Admin/'.$modelName.'Controller.php');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return 'api.admin.controllers.controller';
    }
}
