<?php
namespace LaravelRocket\Generator\Generators\APIs\OAS;

use LaravelRocket\Generator\Generators\APIBaseGenerator;
use function ICanBoogie\pluralize;

class ResponseGenerator extends APIBaseGenerator
{
    protected $type;

    protected function canGenerate(): bool
    {
        $skipResponses = ['List'];

        if (in_array($this->name, $skipResponses)) {
            return false;
        }

        return true;
    }

    /**
     * @throws \Exception
     */
    protected function preprocess()
    {
        $this->detectType();
    }

    /**
     * @return string
     */
    protected function getPath(): string
    {
        return app_path('Http/Response/Api/'.$this->versionNamespace.'/'.$this->name.'.php');
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

        return 'api.responses.array';
    }

    /**
     * @return array
     *
     * @throws \Exception
     */
    protected function getVariables(): array
    {
        $variables               = $this->getBasicVariables();
        $variables['properties'] = $this->getProperties();

        return $variables;
    }

    protected function getProperties()
    {
        $result = [];

        $properties = $this->definition->properties;
        if (is_array($properties)) {
            foreach ($properties as $name => $definition) {
                $result[] = [
                    'name'    => $name,
                    'default' => '',
                ];
                if ($this->type == 'model') {
                    $result['columnName'] = '';
                }
            }
        }
    }

    /**
     * @throws \Exception
     */
    protected function detectType()
    {
        $this->type = 'array';

        if (!empty($this->definition->allOf)) {
            $this->type = 'list';
        } else {
            $tableCandidateName = pluralize(snake_case($this->name));
            $tables             = $this->databaseService->getAllTables();
            if (in_array($tableCandidateName, $tables)) {
                $this->type = 'model';
            }
        }
    }
}
