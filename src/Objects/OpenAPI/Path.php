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

    /**
     * Path constructor.
     *
     * @param string $path
     * @param string $method
     * @param $data
     */
    public function __construct($path, $method, $data)
    {
        $this->path   = $path;
        $this->method = $method;
        $this->data   = $data;

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
        $this->actions = Action::getAllCandidates($this->elements, $this->method, $this->path, $this->data);
    }
}
