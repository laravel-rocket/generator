<?php
namespace LaravelRocket\Generator\Objects\OpenAPI;

class Path
{
    protected $path;

    public function __construct($path, $method, $data)
    {
        $this->path = $path;
    }
}
