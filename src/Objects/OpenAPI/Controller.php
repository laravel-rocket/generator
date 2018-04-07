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

    public function getRequiredRepositoryNames(): array
    {
        return $this->repositoryNames;
    }

    public function getRequiredRequestNames(): array
    {
        return $this->requestNames;
    }

    public function setRepositoryNames()
    {
        $ret = [];
        foreach ($this->actions as $action) {
            $ret[] = $action->getRepositoryName();
        }

        $this->repositoryNames = array_unique($ret);
    }

    public function setRequestNames()
    {
        $ret = [];
        foreach ($this->actions as $action) {
            $ret[] = $action->getRequest()->getName();
        }

        $this->requestNames = array_unique($ret);
    }

    public function findRequest($name)
    {
        foreach ($this->actions as $action) {
            if ($action->getRequest()->getName() === $name) {
                return $action->getRequest();
            }
        }

        return null;
    }
}
