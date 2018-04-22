<?php
namespace LaravelRocket\Generator\Objects\OpenAPI;

use LaravelRocket\Generator\Objects\Table;

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

    /** @var string */
    protected $versionNamespace;

    public function __construct($document, $tables, $json)
    {
        $this->document = $document;
        $this->json     = $json;
        $this->tables   = $tables;

        $this->setResponseDefinition();
        $this->setControllers();
        $this->setVersion();
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
     * @return string
     */
    public function getVersionNamespace(): string
    {
        return $this->versionNamespace;
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
     * @param string $name
     *
     * @return \LaravelRocket\Generator\Objects\Table|null
     */
    public function findTable(string $name)
    {
        foreach ($this->tables as $table) {
            if ($table->getName() === $name) {
                return new Table($table, $this->tables, $this->json);
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
     * @return \LaravelRocket\Generator\Objects\OpenAPI\Action[]
     */
    public function getActions()
    {
        $actions = [];
        foreach ($this->controllers as $controller) {
            foreach ($controller->getActions() as $action) {
                $actions[$action->getHttpMethod().':'.$action->getPath()] = $action;
            }
        }

        return $actions;
    }

    /**
     * @param string $key
     *
     * @return \LaravelRocket\Generator\Objects\OpenAPI\Action|null
     */
    public function findAction($key)
    {
        $actions = $this->getActions();

        return array_key_exists($key, $actions) ? $actions[$key] : null;
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

        $res = [];
        foreach ($this->definitions as $definition) {
            $definition->setListResponseItem($this);
            $res[] = $definition;
        }

        $this->definitions = $res;
    }

    protected function setControllers()
    {
        $controllers = [];
        $paths       = $definitions = $this->document->paths;
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

    protected function setVersion()
    {
        $version   = $this->getDocument()->info->version;
        $fragments = explode('.', $version);
        $major     = (int) $fragments[0];
        if ($major < 0) {
            $major = 1;
        }
        $this->versionNamespace = 'V'.$major;
    }
}
