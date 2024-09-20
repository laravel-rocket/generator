<?php

namespace LaravelRocket\Generator\Generators\APIs\OpenAPI;

use LaravelRocket\Generator\Generators\APIBaseGenerator;

class ResponseGenerator extends APIBaseGenerator
{
    /** @var string */
    protected $type;

    /** @var \LaravelRocket\Generator\Objects\Table */
    protected $table;

    /** @var \LaravelRocket\Generator\Objects\OpenAPI\Definition */
    protected $definition;

    /**
     * @return bool
     */
    protected function canGenerate(): bool
    {
        $skipResponses = ['List', 'Status', 'AccessToken'];

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
        $this->definition = $this->spec->findDefinition($this->name);
    }

    /**
     * @return string
     */
    protected function getPath(): string
    {
        return app_path('Http/Responses/Api/'.$this->versionNamespace.'/'.$this->definition->getName().'.php');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        switch ($this->definition->getType()) {
            case 'model':
                return 'api.oas.responses.model';
            case 'list':
                return 'api.oas.responses.list';
            case 'array':
                return 'api.oas.responses.array';
        }

        return 'api.oas.responses.array';
    }

    /**
     * @return array
     *
     * @throws \Exception
     */
    protected function getVariables(): array
    {
        $variables                  = $this->getBasicVariables();
        $variables['properties']    = $this->definition->getProperties();
        $variables['className']     = $this->name;
        $variables['modelName']     = $this->definition->getModelName();
        $variables['listClassName'] = $this->definition->getListItemName();

        return $variables;
    }
}
