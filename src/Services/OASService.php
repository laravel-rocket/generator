<?php
namespace LaravelRocket\Generator\Services;

use LaravelRocket\Generator\Objects\OpenAPI\OpenAPISpec;
use TakaakiMizuno\SwaggerParser\Parser as SwaggerParser;

class OASService
{
    /** @var \TakaakiMizuno\SwaggerParser\Objects\V20\Document */
    protected $document;

    /** @var \LaravelRocket\Generator\Objects\Definitions|null */
    protected $json;

    /** @var \TakaakiMizuno\MWBParser\Elements\Table[] */
    protected $tables;

    /** @var string $path */
    protected $path;

    /**
     * @param string                                       $path
     * @param \TakaakiMizuno\MWBParser\Elements\Table[]    $tables
     * @param \LaravelRocket\Generator\Objects\Definitions $json
     *
     * @return OpenAPISpec
     */
    public function parse($path, $tables, $json)
    {
        $this->path     = $path;
        $parser         = new SwaggerParser();
        $this->document = $parser->parseFile($path);

        $this->tables = $tables;
        $this->json   = $json;

        $documentObject = new OpenAPISpec($this->document, $this->tables, $this->json);

        return $documentObject;
    }
}
