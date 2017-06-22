<?php
namespace LaravelRocket\Generator\Objects\Swagger;

class Parameter
{
    /**
     * @var \Swagger\Object\Schema
     */
    protected $definition;

    /** @var string $name */
    protected $name;

    /** @var array $properties */
    protected $properties;

    public function __construct($name, $definition)
    {
        $this->definition = $definition;
        $this->name       = $name;
        $this->properties = [];
    }

    public function getName()
    {
        return $this->name;
    }
}
