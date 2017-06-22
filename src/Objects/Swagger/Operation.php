<?php
namespace LaravelRocket\Generator\Objects\Swagger;

class Operation
{
    /**
     * @var \Swagger\Object\Schema
     */
    protected $operation;

    /** @var string $name */
    protected $name;

    /** @var array $properties */
    protected $properties;

    public function __construct($name, $operation)
    {
        $this->operation = $operation;
        $this->name      = $name;
        $this->path      = [];
    }

    public function getName()
    {
        return $this->name;
    }
}
