<?php

namespace LaravelRocket\Generator\Generators;

class APIBaseGenerator extends BaseGenerator
{
    /**
     * @var string
     */
    protected $versionNamespace = 'V1';

    /** @var string $name */
    protected $name;

    /**
     * @param \TakaakiMizuno\SwaggerParser\Objects\Base $object
     */
    protected $object;

    /**
     * @var \LaravelRocket\Generator\Objects\Table[]
     */
    protected $tables;

    /**
     * @param \TakaakiMizuno\SwaggerParser\Objects\V20\Document $osa
     */
    protected $osa;

    /**
     * @var \LaravelRocket\Generator\Objects\Definitions
     */
    protected $json;

    /** @var \LaravelRocket\Generator\Services\DatabaseService $database */
    protected $databaseService;

    /** @var \LaravelRocket\Generator\Objects\OpenAPI\OpenAPISpec */
    protected $spec;

    /**
     * @param string                                               $name
     * @param \LaravelRocket\Generator\Objects\OpenAPI\OpenAPISpec $spec
     * @param \LaravelRocket\Generator\Services\DatabaseService    $databaseService
     * @param \LaravelRocket\Generator\Objects\Definitions         $json
     * @param \TakaakiMizuno\MWBParser\Elements\Table[]            $tables
     *
     * @return bool
     */
    public function generate($name, $spec, $databaseService, $json, $tables): bool
    {
        $this->spec            = $spec;
        $this->json            = $json;
        $this->tables          = $tables;
        $this->databaseService = $databaseService;

        $this->setTarget($name, $spec);
        $this->setVersion();
        $this->preprocess();

        if (!$this->canGenerate()) {
            return false;
        }

        $variables = $this->getVariables();

        $view = $this->getView();
        $path = $this->getPath();

        if (file_exists($path)) {
            unlink($path);
        }
        $this->fileService->render($view, $path, $variables);

        return true;
    }

    /**
     * @param string                                               $name
     * @param \LaravelRocket\Generator\Objects\OpenAPI\OpenAPISpec $spec
     */
    public function setTarget($name, $spec)
    {
        $this->name = $name;
        $this->spec = $spec;
    }

    protected function canGenerate(): bool
    {
        return $this->rebuild || !file_exists($this->getPath());
    }

    protected function getBasicVariables()
    {
        $data = [
            'name'             => $this->name,
            'versionNamespace' => $this->versionNamespace,
        ];

        return $data;
    }

    protected function preprocess()
    {
    }

    /**
     * @return array
     */
    protected function getVariables(): array
    {
        return [];
    }

    protected function setVersion()
    {
        $this->versionNamespace = $this->spec->getVersionNamespace();
    }
}
