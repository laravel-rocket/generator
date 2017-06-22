<?php
namespace LaravelRocket\Generator\Objects\Swagger;

class Definition
{
    /**
     * @var \Swagger\Object\Schema
     */
    protected $definition;

    /** @var string $name */
    protected $name;

    /** @var string $parent */
    protected $parent;

    /** @var Property[] $properties */
    protected $properties;

    public function __construct($name, $definition)
    {
        $this->data       = $definition;
        $this->name       = $name;
        $this->inherit    = null;
        $this->properties = [];

        if (property_exists($this->data, 'allOf')) {
            foreach ($this->data->allOf as $value) {
                if (property_exists($value, '$ref')) {
                    $parent = $value->{'$ref'};
                    if (preg_match('/\/(.+$)/', $parent, $matches)) {
                        $this->parent = $matches[1];
                    }
                }
            }
        }
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        if (count($this->properties)) {
            return $this->properties;
        }

        if (property_exists($this->data, 'allOf')) {
            foreach ($this->data->allOf as $value) {
                if (property_exists($value, 'properties')) {
                    foreach ($value->properties as $name => $property) {
                        $this->properties[] = new Property($name, $property);
                    }
                }
            }
        } elseif (property_exists($this->data, 'properties')) {
            foreach ($this->data->properties as $name => $property) {
                $this->properties[] = new Property($name, $property);
            }
        }

        return $this->properties;
    }
}
