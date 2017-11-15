<?php
namespace LaravelRocket\Generator\Generators;

use LaravelRocket\Generator\Generators\API\ResponseGenerator;
use LaravelRocket\Generator\Generators\API\RouteGenerator;
use LaravelRocket\Generator\Services\SwaggerService;

class APIGenerator extends Generator
{
    protected $document;

    protected $generators = [
        RouteGenerator::class,
        ResponseGenerator::class,
    ];

    public function generate($name, $overwrite = false, $baseDirectory = null, $additionalData = [])
    {
        $ret = $this->readSwaggerFile($name);
        if (!$ret) {
            return;
        }

        foreach ($this->generators as $generator) {
            $object = new $generator();
            $object->generate($name, $overwrite, $baseDirectory, [
                'document' => $this->document,
            ]);
        }
    }

    /**
     * @param string $swaggerPath
     *
     * @return bool
     */
    protected function readSwaggerFile($swaggerPath)
    {
        $swaggerService = new SwaggerService();
        $this->document = $swaggerService->parse($swaggerPath);

        if (empty($this->document)) {
            print 'Swagger File Parse Error'.PHP_EOL;

            return false;
        }

        return true;
    }
}
