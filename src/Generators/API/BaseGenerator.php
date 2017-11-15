<?php
namespace LaravelRocket\Generator\Generators\API;

use LaravelRocket\Generator\Generators\Generator;

class BaseGenerator extends Generator
{
    /** @var \TakaakiMizuno\SwaggerParser\Objects\V20\Document */
    protected $document;

    protected $namespace;

    public function generate($swaggerPath, $overwrite = false, $baseDirectory = null, $additionalData = [])
    {
        $this->document = array_get($additionalData, 'document');
        $this->setNamespace();
        $this->execute();
    }

    protected function execute()
    {
    }

    protected function setNamespace()
    {
        $basePath = $this->document->basePath;
        $names    = array_filter(explode('/', $basePath), function ($path) {
            return !empty($path);
        });

        $this->namespace = implode('\\', array_map(function ($path) {
            return studly_case($path);
        }, $names));
    }
}
