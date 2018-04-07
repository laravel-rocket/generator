<?php
namespace LaravelRocket\Generator\Objects\OpenAPI;

class OpenAPISpec
{
    /**
     * @var \TakaakiMizuno\MWBParser\Elements\Table[]
     */
    protected $tables;

    /** @var \LaravelRocket\Generator\Objects\Definitions|null */
    protected $json;

    /** @var \TakaakiMizuno\SwaggerParser\Objects\V20\Document */
    protected $document;

    /** @var \LaravelRocket\Generator\Objects\OpenAPI\Definition[] */
    protected $definitions;

    /** @var \LaravelRocket\Generator\Objects\OpenAPI\Controller[] */
    protected $controllers;

    public function __construct($document, $tables, $json)
    {
        $this->document = $document;
        $this->json     = $json;
        $this->tables   = $tables;

        $this->setResponseDefinition();
        $this->setControllers();
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function normalizeDefinitionName(string $name): string
    {
        $elements = explode('/', $name);

        return $elements[count($elements) - 1];
    }

    /**
     * @return \LaravelRocket\Generator\Objects\OpenAPI\Definition[]
     */
    public function getDefinitions()
    {
        return $this->definitions;
    }

    /**
     * @return \TakaakiMizuno\SwaggerParser\Objects\V20\Document
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @param string $name
     *
     * @return \LaravelRocket\Generator\Objects\OpenAPI\Definition|null
     */
    public function findDefinition(string $name)
    {
        $name = $this->normalizeDefinitionName($name);

        foreach ($this->definitions as $definition) {
            if ($definition->getName() === $name) {
                return $definition;
            }
        }

        return null;
    }

    /**
     * @return \LaravelRocket\Generator\Objects\OpenAPI\Controller[]
     */
    public function getControllers()
    {
        return $this->controllers;
    }

    /**
     * @param string $name
     *
     * @return \LaravelRocket\Generator\Objects\OpenAPI\Controller|null
     */
    public function findController(string $name)
    {
        foreach ($this->controllers as $controller) {
            if ($controller->getName() === $name) {
                return $controller;
            }
        }

        return null;
    }

    protected function setResponseDefinition()
    {
        $definitions = $this->document->definitions;
        foreach ($definitions as $name => $definition) {
            $this->definitions[] = new Definition($name, $definition, $this->json, $this->document, $this->tables);
        }
    }

    protected function setControllers()
    {
        $controllers       = [];
        $paths             = $definitions = $this->document->paths;
        foreach ($paths as $path => $pathInfo) {
            $methods = $pathInfo->getMethods();
            foreach ($methods as $method => $info) {
                $pathObject = new Path($path, $method, $info, $this);
                foreach ($pathObject->getActions() as $action) {
                    if (!$this->checkActionAlreadyExists($action, $controllers)) {
                        if (!array_key_exists($action->getControllerName(), $controllers)) {
                            $this->controllers[$action->getControllerName()] = [];
                        }
                        $controllers[$action->getControllerName()][$action->getMethod()] = $action;
                        break;
                    }
                }
            }
        }

        $this->controllers = [];
        foreach ($controllers as $name => $controller) {
            $this->controllers[] = new Controller($name, $controllers[$name], $this);
        }
    }

    /**
     * @param \LaravelRocket\Generator\Objects\OpenAPI\Action $action
     * @param array                                           $controllers
     *
     * @return bool
     */
    protected function checkActionAlreadyExists($action, $controllers)
    {
        $controllerName = $action->getControllerName();
        if (!array_key_exists($controllerName, $controllers)) {
            return false;
        }

        if (!array_key_exists($action->getMethod(), $controllers[$controllerName])) {
            return false;
        }

        return true;
    }
}
