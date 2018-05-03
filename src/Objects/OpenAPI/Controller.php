<?php
namespace LaravelRocket\Generator\Objects\OpenAPI;

use LaravelRocket\Generator\Objects\Relation;

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

    /** @var \LaravelRocket\Generator\Objects\OpenAPI\Request[] */
    protected $requests = [];

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
        $this->setResponseNames();
        $this->setRequestNames();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return \LaravelRocket\Generator\Objects\OpenAPI\Action[]
     */
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
            if ($action->getAction() === $name) {
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
        $ret = [];
        foreach ($this->requests as $request) {
            $ret[] = $request->getName();
        }

        return $ret;
    }

    /**
     * @return \LaravelRocket\Generator\Objects\OpenAPI\Request[]
     */
    public function getRequiredRequests(): array
    {
        return $this->requests;
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
            if (!empty($action->getTargetTable())) {
                $ret[] = $action->getTargetTable()->getModelName().'Repository';
            }
            if (!empty($action->getParentTable())) {
                $ret[] = $action->getParentTable()->getModelName().'Repository';
            }
            if (!empty($action->getParentRelation()) && $action->getParentRelation()->getType() === Relation::TYPE_BELONGS_TO_MANY) {
                $ret[] = $action->getParentRelation()->getIntermediateTableModel().'Repository';
            }
        }

        $this->repositoryNames = array_unique($ret);
    }

    protected function setRequestNames()
    {
        $requests = [];
        foreach ($this->actions as $action) {
            $request                       = $action->getRequest();
            $requests[$request->getName()] = $request;
        }

        $this->requests = $requests;
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
