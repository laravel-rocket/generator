<?php
namespace LaravelRocket\Generator\Objects\OpenAPI;

use function ICanBoogie\pluralize;
use function ICanBoogie\singularize;

class Action
{
    public const CONTEXT_TYPE_LIST        = 'list';
    public const CONTEXT_TYPE_SHOW        = 'show';
    public const CONTEXT_TYPE_STORE       = 'store';
    public const CONTEXT_TYPE_UPDATE      = 'update';
    public const CONTEXT_TYPE_DESTROY     = 'destroy';
    public const CONTEXT_TYPE_ME          = 'me';
    public const CONTEXT_TYPE_ME_SUB_DATA = 'me_sub_data';
    public const CONTEXT_TYPE_AUTH        = 'auth';
    public const CONTEXT_TYPE_AUTH_SNS    = 'auth_sns';
    public const CONTEXT_TYPE_UNKNOWN     = 'unknown';
    public const CONTEXT_TYPE_PASSWORD    = 'password';

    protected const SPECIAL_ACTIONS = [
        'post:signin'          => [
            'controller' => 'Auth',
            'action'     => 'postSignIn',
            'type'       => self::CONTEXT_TYPE_AUTH,
        ],
        'post:signup'          => [
            'controller' => 'Auth',
            'action'     => 'postSignUp',
            'type'       => self::CONTEXT_TYPE_AUTH,
        ],
        'post:signout'         => [
            'controller' => 'Auth',
            'action'     => 'postSignOut',
            'type'       => self::CONTEXT_TYPE_AUTH,
        ],
        'post:forgot-password' => [
            'controller' => 'Password',
            'action'     => 'forgotPassword',
            'type'       => self::CONTEXT_TYPE_PASSWORD,
        ],
        'post:token/refresh'   => [
            'controller' => 'Auth',
            'action'     => 'postRefreshToken',
            'type'       => self::CONTEXT_TYPE_AUTH,
        ],
        'get:me'               => [
            'controller' => 'Me',
            'action'     => 'getMe',
            'type'       => self::CONTEXT_TYPE_ME,
        ],
        'put:me'               => [
            'controller' => 'Me',
            'action'     => 'putMe',
            'type'       => self::CONTEXT_TYPE_ME,
        ],
    ];

    protected const SPECIAL_PATH_NAMES = [
        'me'    => [
            'model' => 'User',
        ],
        'image' => [
            'model' => 'File',
        ],
    ];

    /** @var string */
    protected $path = '';

    /** @var string */
    protected $action = '';

    /** @var string */
    protected $httpMethod = '';

    /** @var string */
    protected $controllerName = '';

    /** @var string */
    protected $type = '';

    /** @var \TakaakiMizuno\SwaggerParser\Objects\Base */
    protected $info;

    /** @var \LaravelRocket\Generator\Objects\OpenAPI\OpenAPISpec */
    protected $spec;

    /** @var bool */
    protected $hasParent = false;

    /** @var \LaravelRocket\Generator\Objects\Table|null */
    protected $targetTable = null;

    /** @var \LaravelRocket\Generator\Objects\Table|null */
    protected $parentTable = null;

    /** @var \LaravelRocket\Generator\Objects\Relation|null */
    protected $parentRelation = null;

    /** @var array */
    protected $parentFilters = [];

    /** @var \LaravelRocket\Generator\Objects\OpenAPI\PathElement[] $elements */
    protected $elements = [];

    /** @var \LaravelRocket\Generator\Objects\OpenAPI\Parameter[] */
    protected $params = [];

    /** @var \LaravelRocket\Generator\Objects\OpenAPI\Definition|null */
    protected $response;

    /** @var \LaravelRocket\Generator\Objects\OpenAPI\Request */
    protected $request;

    /** @var string */
    protected $snsName = '';

    /**
     * Action constructor.
     *
     * @param string                                               $path
     * @param string                                               $httpMethod
     * @param \TakaakiMizuno\SwaggerParser\Objects\Base            $info
     * @param \LaravelRocket\Generator\Objects\OpenAPI\OpenAPISpec $spec
     */
    public function __construct($path, $httpMethod, $info, $spec)
    {
        $this->path       = $path;
        $this->httpMethod = $httpMethod;
        $this->info       = $info;
        $this->spec       = $spec;

        $this->parse();
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return string
     */
    public function getHttpMethod(): string
    {
        return $this->httpMethod;
    }

    /**
     * @return string
     */
    public function getControllerName(): string
    {
        return $this->controllerName;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function hasParent(): string
    {
        return $this->hasParent;
    }

    /**
     * @return string
     */
    public function getTargetTable(): string
    {
        return $this->targetTable;
    }

    /**
     * @return \LaravelRocket\Generator\Objects\Table
     */
    public function getParentTable(): string
    {
        return $this->parentTable;
    }

    /**
     * @return array
     */
    public function getParentFilters(): array
    {
        return $this->parentFilters;
    }

    /**
     * @return string[]
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @return string[]
     */
    public function getParamNames(): array
    {
        $ret = [];
        foreach ($this->params as $parameter) {
            $ret[] = '$'.$parameter->getName();
        }

        return $ret;
    }

    /**
     * @return string[]
     */
    public function getQueryParameters(): array
    {
        $ret = [];
        foreach ($this->info->parameters as $parameter) {
            if ($parameter->in === 'query') {
                $ret[] = $parameter->name;
            }
        }

        return $ret;
    }

    /**
     * @return string[]
     */
    public function getBodyParameters(): array
    {
        $ret = [];
        foreach ($this->info->parameters as $parameter) {
            if ($parameter->in === 'formData') {
                $ret[] = $parameter->name;
            }
        }

        return $ret;
    }

    /**
     * @return \TakaakiMizuno\SwaggerParser\Objects\Base
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * @return \LaravelRocket\Generator\Objects\OpenAPI\Definition
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return \LaravelRocket\Generator\Objects\OpenAPI\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return string
     */
    public function getSnsName()
    {
        return $this->snsName;
    }

    /**
     * @return string
     */
    public function getRouteName(): string
    {
        return $this->controllerName.'Controller@'.$this->action;
    }

    /**
     * @return string
     */
    public function getRouteIdentifier(): string
    {
        return camel_case(lcfirst($this->controllerName)).'.'.$this->action;
    }

    protected function parse()
    {
        $this->elements = array_reverse(PathElement::parsePath($this->path, $this->httpMethod));
        $this->setControllerAndAction();
        $this->setRelationWithParent();
        $this->setParams();
        $this->setResponse();
        $this->setRequest();
    }

    protected function setResponse()
    {
        $responses = $this->info->responses;
        foreach ($responses as $statusCode => $response) {
            if (substr($statusCode, 0, 1) === '2') {
                $schema         = $response->schema;
                $ref            = $schema->{'$ref'};
                $this->response = $this->spec->findDefinition($ref);
                if ($this->httpMethod === 'delete') {
                } elseif ($this->response->getType() === Definition::TYPE_MODEL) {
                    $model                = $this->response->getModelName();
                    $this->repositoryName = $model.'Repository';
                } elseif ($this->response->getType() === Definition::TYPE_LIST) {
                    $model                = $this->response->getListItem()->getModelName();
                    $this->repositoryName = $model.'Repository';
                }

                return;
            }
        }

        $this->response = null;
    }

    protected function setRequest()
    {
        $this->request = new Request($this->controllerName, $this->action, $this->httpMethod, $this->info, $this->response, $this->spec);
    }

    protected function setParams()
    {
        $this->params = [];
        $parameters   = $this->info->parameters;
        foreach ($parameters as $parameter) {
            if ($parameter->in === 'path') {
                $this->params[] = new Parameter($parameter, $this->spec);
            }
        }
    }

    /**
     * @param int $index
     *
     * @return string
     */
    protected function getModelFromPathElement($index)
    {
        $pathElement = $this->elements[$index];

        $name = snake_case(pluralize($pathElement->elementName()));

        $table = $this->spec->findTable($name);
        if (!empty($table)) {
            return $table->getModelName();
        }

        $name = snake_case(singularize($pathElement->elementName()));
        if (array_key_exists($name, self::SPECIAL_PATH_NAMES)) {
            return self::SPECIAL_PATH_NAMES[$name]['model'];
        }

        return 'User';
    }

    protected function setControllerAndAction()
    {
        $this->type = self::CONTEXT_TYPE_UNKNOWN;

        $path       = starts_with($this->path, '/') ? substr($this->path, 1) : $this->path;
        $specialKey = implode(':', [$this->httpMethod, $path]);
        if (array_key_exists($specialKey, self::SPECIAL_ACTIONS)) {
            $actionInfo           = self::SPECIAL_ACTIONS[$specialKey];
            $this->action         = array_get($actionInfo, 'action', '');
            $this->controllerName = array_get($actionInfo, 'controller', '');
            $this->type           = array_get($actionInfo, 'type', '');

            return;
        }

        // Check SNS SignIn
        if ($this->httpMethod === 'post' && preg_match('/^\/?signin\/([^\/]+)$/', $this->path, $matches)) {
            $name                 = camel_case($matches[1]);
            $this->action         = 'post'.ucfirst($name).'SignIn';
            $this->controllerName = ucfirst($name).'Auth';
            $this->type           = self::CONTEXT_TYPE_AUTH_SNS;
            $this->snsName        = $name;

            return;
        }

        $params = [];
        foreach ($this->elements as $element) {
            if ($element->isVariable()) {
                $params[] = $element->variableName();
            }
            $params = array_reverse($params);
        }

        switch (count($this->elements)) {
            case 1:
                $element              = $this->elements[0];
                $this->controllerName = $element->getModelName();
                $this->targetTable    = $this->spec->findTable($this->elements[0]);

                if ($element->isPlural()) {
                    $this->controllerName = $element->getModelName();
                    switch ($this->httpMethod) {
                        case 'get':
                            $this->type   = self::CONTEXT_TYPE_LIST;
                            $this->action = 'index';

                            return;
                        case 'post':
                            $this->type   = self::CONTEXT_TYPE_STORE;
                            $this->action = 'store';

                            return;
                    }
                }
                $this->action = $this->httpMethod.ucfirst($element->elementName());
                $this->type   = self::CONTEXT_TYPE_UNKNOWN;

                return;
            case 2:
                $element              = $this->elements[1];
                $subElement           = $this->elements[0];
                $this->controllerName = $element->getModelName();
                $this->type           = self::CONTEXT_TYPE_UNKNOWN;
                $this->action         = $subElement->elementName();

                if ($element->elementName() === 'me') {
                    $this->controllerName = 'Me';
                    $this->type           = self::CONTEXT_TYPE_ME_SUB_DATA;
                    $this->action         = $this->httpMethod.ucfirst($subElement->elementName());
                    $this->parentTable    = $this->spec->findTable('users');
                    $this->targetTable    = $this->spec->findTable($this->elements[0]);

                    return;
                }

                if ($element->isPlural() && $subElement->isVariable()) {
                    $this->targetTable = $this->spec->findTable($this->elements[1]);
                    switch ($this->httpMethod) {
                        case 'get':
                            $this->type   = self::CONTEXT_TYPE_SHOW;
                            $this->action = 'show';

                            return;
                        case 'put':
                        case 'patch':
                        case 'post':
                            $this->type   = self::CONTEXT_TYPE_UPDATE;
                            $this->action = 'update';

                            return;
                        case 'delete':
                            $this->type   = self::CONTEXT_TYPE_DESTROY;
                            $this->action = 'destroy';

                            return;
                    }
                }

                $this->targetTable    = $this->spec->findTable($this->elements[1]);
                $this->controllerName = $element->getModelName();
                $this->action         = $this->httpMethod.ucfirst($subElement->elementName());
                $this->type           = self::CONTEXT_TYPE_UNKNOWN;

                return;
            case 3:
                $parentElement        = $this->elements[2];
                $subElement           = $this->elements[1];
                $targetElement        = $this->elements[0];
                $this->controllerName = $parentElement->getModelName();

                if ($parentElement->isPlural() && $subElement->isVariable()) {
                    $this->hasParent   = true;
                    $this->parentTable = $this->spec->findTable($this->elements[2]);
                    $this->targetTable = $this->spec->findTable($this->elements[0]);

                    $key                 = snake_case($this->parentTable->getModelName().'_'.$subElement->variableName());
                    $this->parentFilters = [
                        $key => $subElement->variableName(),
                    ];

                    switch ($this->httpMethod) {
                        case 'get':
                            if ($targetElement->isPlural()) {
                                $this->type   = self::CONTEXT_TYPE_LIST;
                                $this->action = 'get'.ucfirst($targetElement->elementName());
                            } else {
                                $this->type   = self::CONTEXT_TYPE_UNKNOWN;
                                $this->action = 'get'.ucfirst($targetElement->elementName());
                            }

                            return;
                        case 'put':
                        case 'patch':
                            $this->type   = self::CONTEXT_TYPE_UPDATE;
                            $this->action = 'update'.ucfirst($targetElement->elementName());

                            return;
                        case 'post':
                            if ($targetElement->isPlural()) {
                                $this->type   = self::CONTEXT_TYPE_UPDATE;
                                $this->action = 'create'.ucfirst($targetElement->elementName());
                            } else {
                                $this->type        = self::CONTEXT_TYPE_UPDATE;
                                $this->action      = 'post'.ucfirst($parentElement->elementName()).ucfirst($targetElement->elementName());
                                $this->targetTable = $this->parentTable;
                                $this->hasParent   = false;
                                $this->parentTable = null;
                            }

                            return;
                        case 'delete':
                            $this->type   = self::CONTEXT_TYPE_DESTROY;
                            $this->action = 'destroy'.ucfirst($targetElement->elementName());

                            return;
                    }
                } elseif ($subElement->isPlural() && $targetElement->isVariable()) {
                    if ($parentElement->elementName() === 'me') {
                        $this->hasParent     = true;
                        $this->parentTable   = $this->spec->findTable('users');
                        $this->parentFilters = [
                            'user_id' => 'id',
                        ];
                        $this->targetTable   = $this->spec->findTable($this->elements[1]);
                    }
                    switch ($this->httpMethod) {
                        case 'get':
                            $this->type   = self::CONTEXT_TYPE_SHOW;
                            $this->action = 'show'.ucfirst($subElement->elementName());

                            return;
                        case 'put':
                        case 'patch':
                        case 'post':
                            $this->type   = self::CONTEXT_TYPE_UPDATE;
                            $this->action = 'update'.ucfirst($subElement->elementName());

                            return;
                        case 'delete':
                            $this->type   = self::CONTEXT_TYPE_DESTROY;
                            $this->action = 'delete'.ucfirst(singularize($subElement->elementName()));

                            return;
                    }
                }
                $this->action = $this->httpMethod.ucfirst($targetElement->elementName());
                $this->type   = self::CONTEXT_TYPE_UNKNOWN;

                return;
        }

        $targetElement = $this->elements[0];
        $this->action  = $this->httpMethod.ucfirst($targetElement->elementName());
        $this->type    = self::CONTEXT_TYPE_UNKNOWN;
    }

    public function setRelationWithParent()
    {
        if (!$this->hasParent) {
            return;
        }

        $this->parentRelation = $this->targetTable->findRelationWithTable($this->parentTable);
    }
}
