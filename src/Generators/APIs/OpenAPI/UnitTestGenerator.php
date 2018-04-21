<?php
namespace LaravelRocket\Generator\Generators\APIs\OpenAPI;

use LaravelRocket\Generator\Generators\APIBaseGenerator;

class UnitTestGenerator extends APIBaseGenerator
{
    /** @var \LaravelRocket\Generator\Objects\OpenAPI\Controller */
    protected $controller;

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
        $this->controller = $this->spec->findController($this->name);
    }

    /**
     * @return string
     */
    protected function getPath(): string
    {
        return base_path('tests/Controllers/Api/'.$this->versionNamespace.'/'.$this->name.'ControllerTest.php');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return 'api.oas.unittest';
    }

    /**
     * @return array
     *
     * @throws \Exception
     */
    protected function getVariables(): array
    {
        $variables               = $this->getBasicVariables();
        $variables['className']  = $this->name.'Controller';
        $variables['controller'] = $this->controller;

        return $variables;
    }
}
