<?php
namespace LaravelRocket\Generator\Tests\Objects;

use LaravelRocket\Generator\Services\SwaggerService;
use LaravelRocket\Generator\Tests\TestCase;

class SwaggerTest extends TestCase
{
    public function testGetFromYaml()
    {
        $dataPath = realpath(__DIR__.'/../data/sample_swagger.yaml');
        $service  = new SwaggerService();
        $document = $service->parse($dataPath);

        $this->assertNotEmpty($document);
        $this->assertEquals('/api/v1', $document->basePath);
    }

    public function testGetFromJson()
    {
        $dataPath = realpath(__DIR__.'/../data/sample_swagger.json');
        $service  = new SwaggerService();
        $document = $service->parse($dataPath);

        $this->assertNotEmpty($document);
        $this->assertEquals('/api/v1', $document->basePath);
    }
}
