<?php
namespace LaravelRocket\Generator\Objects\OpenAPI;

class Path
{
    /** @var string */
    protected $path;

    /** @var string */
    protected $method;

    protected $data;

    /** @var \LaravelRocket\Generator\Objects\OpenAPI\PathElement[] */
    protected $elements;

    /** @var \LaravelRocket\Generator\Objects\OpenAPI\Action */
    protected $action;

    /** @var \LaravelRocket\Generator\Objects\OpenAPI\OpenAPISpec */
    protected $spec;

    /**
     * Path constructor.
     *
     * @param string $path
     * @param string $method
     * @param $data
     * @param \LaravelRocket\Generator\Objects\OpenAPI\OpenAPISpec $spec
     */
    public function __construct($path, $method, $data, $spec)
    {
        $this->path   = $path;
        $this->method = $method;
        $this->data   = $data;
        $this->spec   = $spec;

        $this->parseElements();
        $this->parseActions();
    }

    /**
     * @return \LaravelRocket\Generator\Objects\OpenAPI\Action
     */
    public function getAction()
    {
        return $this->action;
    }

    protected function parseElements()
    {
        $this->elements = PathElement::parsePath($this->path, $this->method);
    }

    protected function parseActions()
    {
        $this->action = new Action($this->path, $this->method, $this->data, $this->spec);
    }
}
