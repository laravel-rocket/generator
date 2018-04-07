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
    public function getName()
    {
        return $this->info->name;
    }

    /**
     * @return bool
     */
    public function isInRequest()
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
    public function isRequired()
    {
        return (bool) $this->info->required;
    }
}
