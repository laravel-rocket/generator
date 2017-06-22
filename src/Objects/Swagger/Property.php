<?php
namespace LaravelRocket\Generator\Objects\Swagger;

class Property
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
        $this->property = $definition;
        $this->name     = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->property->type;
    }
}
