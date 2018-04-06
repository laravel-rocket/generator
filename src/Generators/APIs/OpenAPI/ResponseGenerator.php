<?php
namespace LaravelRocket\Generator\Generators\APIs\OpenAPI;

use LaravelRocket\Generator\Generators\APIBaseGenerator;
use LaravelRocket\Generator\Objects\OpenAPI\Definition;

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
        $skipResponses = ['List'];

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
        $this->definition = new Definition($this->name, $this->object, $this->json, $this->osa, $this->tables);
    }

    /**
     * @return string
     */
    protected function getPath(): string
    {
        return app_path('Http/Response/Api/'.$this->versionNamespace.'/'.$this->definition->getName().'.php');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        switch ($this->type) {
            case 'model':
                return 'api.responses.model';
            case 'list':
                return 'api.responses.list';
            case 'array':
                return 'api.responses.array';
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
        $variables               = $this->getBasicVariables();
        $variables['properties'] = $this->definition->getProperties();

        return $variables;
    }
}
