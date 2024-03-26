<?php

namespace LaravelRocket\Generator\Generators\APIs\Admin;

class ResponseGenerator extends BaseAdminAPIGenerator
{
    /**
     * @return string
     */
    protected function getPath(): string
    {
        $modelName = $this->getModelName();

        return app_path('Http/Responses/Api/Admin/'.$modelName.'.php');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return 'api.admin.response';
    }
}
