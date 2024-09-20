<?php

namespace LaravelRocket\Generator\Generators\APIs\OpenAPI;

use LaravelRocket\Generator\Generators\APIBaseGenerator;

class RequestGenerator extends APIBaseGenerator
{
    /** @var string */
    protected $type;

    /** @var \LaravelRocket\Generator\Objects\Table */
    protected $table;

    /** @var \LaravelRocket\Generator\Objects\OpenAPI\Request */
    protected $request;

    /**
     * @return bool
     */
    protected function canGenerate(): bool
    {
        $skipResponses = ['PaginationRequest', 'Request'];

        if (in_array($this->name, $skipResponses)) {
            return false;
        }

        return $this->rebuild || !file_exists($this->getPath());
    }

    /**
     * @throws \Exception
     */
    protected function preprocess()
    {
        foreach ($this->spec->getControllers() as $controller) {
            $request = $controller->findRequest($this->name);
            if (!empty($request)) {
                $this->request = $request;
            }
        }

        return null;
    }

    /**
     * @return string
     */
    protected function getPath(): string
    {
        return app_path('Http/Requests/Api/'.$this->versionNamespace.'/'.$this->name.'.php');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return 'api.oas.request';
    }

    /**
     * @return array
     *
     * @throws \Exception
     */
    protected function getVariables(): array
    {
        $variables                  = $this->getBasicVariables();
        $variables['request']       = $this->request;
        $variables['className']     = $this->request->getName();

        return $variables;
    }
}
