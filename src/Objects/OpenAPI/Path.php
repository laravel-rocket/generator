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

    /** @var \LaravelRocket\Generator\Objects\OpenAPI\Action[] */
    protected $actions;

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
     * @return \LaravelRocket\Generator\Objects\OpenAPI\Action[]
     */
    public function getActions()
    {
        return $this->actions;
    }

    protected function parseElements()
    {
        $this->elements = PathElement::parsePath($this->path, $this->method);
    }

    protected function parseActions()
    {
        $this->actions = Action::getAllCandidates($this->elements, $this->method, $this->path, $this->data, $this->spec);
    }
}
