<?php
namespace LaravelRocket\Generator\Generators\APIs\OpenAPI;

use LaravelRocket\Generator\Generators\APIBaseGenerator;

class ControllerGenerator extends APIBaseGenerator
{
    /**
     * @return bool
     */
    protected function canGenerate(): bool
    {
        return $this->rebuild || !file_exists($this->getPath());
    }

    /**
     * @throws \Exception
     */
    protected function preprocess()
    {
    }

    /**
     * @return string
     */
    protected function getPath(): string
    {
        return app_path('Http/Controllers/Api/'.$this->versionNamespace.'/'.$this->name.'Controller.php');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return 'api.oas.controller';
    }
}
