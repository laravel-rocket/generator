<?php
namespace LaravelRocket\Generator\Services;

use TakaakiMizuno\SwaggerParser\Parser as SwaggerParser;

class SwaggerService
{
    protected $document;

    protected $path;

    /**
     * @param $path
     *
     * @return null|\TakaakiMizuno\SwaggerParser\Objects\V20\Document
     */
    public function parse($path)
    {
        $this->path = $path;
        $parser     = new SwaggerParser();

        return $parser->parseFile($path);
    }
}
