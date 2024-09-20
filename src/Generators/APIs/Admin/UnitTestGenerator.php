<?php

namespace LaravelRocket\Generator\Generators\APIs\Admin;

class UnitTestGenerator extends BaseAdminAPIGenerator
{
    /**
     * @return string
     */
    protected function getPath(): string
    {
        $modelName = $this->getModelName();

        return base_path('tests/Controllers/Api/Admin/'.$modelName.'ControllerTest.php');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return 'api.admin.unittest';
    }
}
