<?php
namespace LaravelRocket\Generator\Objects\Swagger;

class Spec
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var \stdClass
     */
    protected $object;

    /**
     * Swagger constructor.
     *
     * @param \stdClass $data
     * @param string    $path
     */
    public function __construct($data, $path)
    {
        $this->path = $path;
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getOperations()
    {
        return $this->data->paths;
    }

    /**
     * @return string
     */
    public function getBasePath()
    {
        return $this->data->basePath;
    }

    /**
     * @return array|\Swagger\Object\Reference
     */
    public function getDefinitions()
    {
        /** @var \Swagger\Object\Definitions $definitions */
        $definitions = $this->data->definitions;
        $ret         = [];
        foreach ($definitions as $name => $definition) {
            $ret[] = new Definition($name, $definition);
        }

        return $ret;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        $basePath = $this->getBasePath();
        $names    = array_filter(explode('/', $basePath), function ($path) {
            return !empty($path);
        });

        return implode('\\', array_map(function ($path) {
            return studly_case($path);
        }, $names));
    }
}
