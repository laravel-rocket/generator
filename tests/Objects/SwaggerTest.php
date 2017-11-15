<?php
namespace LaravelRocket\Generator\Tests\Objects;

use LaravelRocket\Generator\Objects\Swagger\Definition;
use LaravelRocket\Generator\Services\SwaggerService;
use LaravelRocket\Generator\Tests\TestCase;

class SwaggerTest extends TestCase
{
    public function testGetFromYaml()
    {
        $dataPath = realpath(__DIR__.'/../data/sample_swagger.yaml');
        $service  = new SwaggerService();
        $spec     = $service->parse($dataPath);

        $this->assertNotEmpty($spec);
        $this->assertEquals('/api/v1', $spec->getBasePath());

        $definitions = $spec->getDefinitions();

        /**
         * @var string
         * @var Definition $definition
         */
        foreach ($definitions as $definition) {
            print $definition->getName().PHP_EOL;
            $properties = $definition->getProperties();
            foreach ($properties as $property) {
                print $property->getName().PHP_EOL;
                print $property->getType().PHP_EOL;
            }
        }

        $this->assertEquals('Api\\V1', $spec->getNamespace());
    }

    public function testGetFromJson()
    {
        $dataPath = realpath(__DIR__.'/../data/sample_swagger.json');
        $service  = new SwaggerService();
        $swagger  = $service->parse($dataPath);

        $this->assertNotEmpty($swagger);
        $this->assertEquals('/api/v1', $swagger->getBasePath());
    }
}
