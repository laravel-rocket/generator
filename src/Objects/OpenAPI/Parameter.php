<?php

namespace LaravelRocket\Generator\Objects\OpenAPI;

class Parameter
{
    /** @var string */
    protected $method;

    /** @var \TakaakiMizuno\SwaggerParser\Objects\Base */
    protected $info;

    /** @var \LaravelRocket\Generator\Objects\OpenAPI\OpenAPISpec */
    protected $spec;

    /**
     * Path constructor.
     *
     * @param \TakaakiMizuno\SwaggerParser\Objects\Base            $info
     * @param \LaravelRocket\Generator\Objects\OpenAPI\OpenAPISpec $spec
     */
    public function __construct($info, $spec)
    {
        $this->info     = $info;
        $this->spec     = $spec;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->info->name;
    }

    /**
     * @return bool
     */
    public function isInRequest(): bool
    {
        $in = $this->info->in;
        switch ($in) {
            case 'query':
            case 'formData':
                return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return (bool) $this->info->required;
    }

    /**
     * @return string
     */
    public function getVariableType(): string
    {
        $in = $this->info->type;
        switch ($in) {
            case 'integer':
                return 'int';
            case 'number':
                return 'float';
            case 'string':
                return 'string';
            case 'boolean':
                return 'boolean';
        }

        return 'mixed';
    }
}
