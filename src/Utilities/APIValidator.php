<?php
namespace LaravelRocket\Generator\Utilities;

class APIValidator
{
    protected $swagger;

    public function __construct($swagger)
    {
        $this->swagger = $swagger;
    }

    public function validate()
    {
    }

    protected function validateBasePath()
    {
    }
}
