<?php
namespace LaravelRocket\Generator\Objects\OpenAPI;

class Controller
{
    /** @var string */
    protected $name;

    /** @var \LaravelRocket\Generator\Objects\OpenAPI\Action[] */
    protected $actions;

    /** @var \LaravelRocket\Generator\Objects\OpenAPI\OpenAPISpec */
    protected $spec;

    /** @var string[] */
    protected $repositoryNames = [];

    /** @var string[] */
    protected $requestNames = [];

    /** @var string[] */
    protected $responseNames = [];

    /**
     * Controller constructor.
     *
     * @param string                                               $name
     * @param \LaravelRocket\Generator\Objects\OpenAPI\Action[]    $actions
     * @param \LaravelRocket\Generator\Objects\OpenAPI\OpenAPISpec $spec
     */
    public function __construct($name, $actions, $spec)
    {
        $this->name    = $name;
        $this->actions = $actions;
        $this->spec    = $spec;

        $this->setRepositoryNames();
        $this->setRequestNames();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * @param string $name
     *
     * @return \LaravelRocket\Generator\Objects\OpenAPI\Action|null
     */
    public function findActionByName(string $name)
    {
        foreach ($this->actions as $action) {
            if ($action->getMethod() === $name) {
                return $action;
            }
        }

        return null;
    }

    /**
     * @return string[]
     */
    public function getRequiredRepositoryNames(): array
    {
        return $this->repositoryNames;
    }

    /**
     * @return string[]
     */
    public function getRequiredRequestNames(): array
    {
        return $this->requestNames;
    }

    /**
     * @return string[]
     */
    public function getRequiredResponseNames(): array
    {
        return $this->responseNames;
    }

    /**
     * @param string $name
     *
     * @return \LaravelRocket\Generator\Objects\OpenAPI\Request|null
     */
    public function findRequest(string $name)
    {
        foreach ($this->actions as $action) {
            if ($action->getRequest()->getName() === $name) {
                return $action->getRequest();
            }
        }

        return null;
    }

    protected function setRepositoryNames()
    {
        $ret = [];
        foreach ($this->actions as $action) {
            if (!empty($action->getRepositoryName())) {
                $ret[] = $action->getRepositoryName();
            }
        }

        $this->repositoryNames = array_unique($ret);
    }

    protected function setRequestNames()
    {
        $ret = [];
        foreach ($this->actions as $action) {
            $ret[] = $action->getRequest()->getName();
        }

        $this->requestNames = array_unique($ret);
    }

    protected function setResponseNames()
    {
        $ret = [];
        foreach ($this->actions as $action) {
            $ret[] = $action->getResponse()->getName();
        }

        $this->responseNames = array_unique($ret);
    }
}
