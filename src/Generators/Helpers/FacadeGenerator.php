<?php
namespace LaravelRocket\Generator\Generators\Helpers;

class FacadeGenerator extends HelperGenerator
{
    /**
     * @return string
     */
    protected function getPath(): string
    {
        return app_path('/Facades/'.$this->name.'Helper.php');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return 'helper.facade';
    }
}
