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
            'controller' => 'AuthController',
            'action'     => 'postSignIn',
            'type'       => self::CONTEXT_TYPE_AUTH,
        ],
        'post:signup'          => [
            'controller' => 'AuthController',
            'action'     => 'postSignUp',
            'type'       => self::CONTEXT_TYPE_AUTH,
        ],
        'post:signout'         => [
            'controller' => 'AuthController',
            'action'     => 'postSignOut',
            'type'       => self::CONTEXT_TYPE_AUTH,
        ],
        'post:forgot-password' => [
            'controller' => 'PasswordController',
            'action'     => 'forgotPassword',
            'type'       => self::CONTEXT_TYPE_PASSWORD,
        ],
        'post:token/refresh'   => [
            'controller' => 'AuthController',
            'action'     => 'postRefreshToken',
            'type'       => self::CONTEXT_TYPE_AUTH,
        ],
        'get:me'               => [
            'controller' => 'MeController',
            'action'     => 'getMe',
            'type'       => self::CONTEXT_TYPE_ME,
        ],
        'put:me'               => [
            'controller' => 'MeController',
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

    /** @var string */
    protected $targetModel = '';

    /** @var string */
    protected $parentModel = '';

    /** @var array */
    protected $parentFilters = [];

    /** @var \LaravelRocket\Generator\Objects\OpenAPI\PathElement[] $elements */
    protected $elements = [];

    /** @var string[] */
    protected $params = [];

    /** @var \LaravelRocket\Generator\Objects\OpenAPI\Definition|null */
    protected $response;

    /** @var \LaravelRocket\Generator\Objects\OpenAPI\Request */
    protected $request;

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
    public function getTargetModel(): string
    {
        return $this->targetModel;
    }

    /**
     * @return string
     */
    public function getParentModel(): string
    {
        return $this->parentModel;
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

    protected function parse()
    {
        $this->elements = array_reverse(PathElement::parsePath($this->path, $this->httpMethod));
        $this->setControllerAndAction();
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
        $specialKey = implode(':', [$this->httpMethod, $this->path]);
        if (array_key_exists($specialKey, self::SPECIAL_ACTIONS)) {
            $actionInfo           = self::SPECIAL_ACTIONS[$specialKey];
            $this->action         = array_get($actionInfo, 'action', '');
            $this->controllerName = array_get($actionInfo, 'controller', '');
            $this->type           = array_get($actionInfo, 'type', '');

            return;
        }

        // Check SNS SignIn
        if ($this->httpMethod === 'post' && preg_match('/^signin\/([^\/]+)$/', $this->path, $matches)) {
            $name                 = camel_case($matches[1]);
            $this->action         = 'post'.ucfirst($name).'SignIn';
            $this->controllerName = ucfirst($name).'AuthController';
            $this->type           = self::CONTEXT_TYPE_AUTH_SNS;

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
                $this->controllerName = ucfirst($element->getModelName()).'Controller';
                $this->targetModel    = $this->getModelFromPathElement(0);

                if ($element->isPlural()) {
                    $this->controllerName = title_case(snake_case(singularize($element->elementName())));
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
                $this->controllerName = ucfirst($element->getModelName()).'Controller';
                $this->type           = self::CONTEXT_TYPE_UNKNOWN;
                $this->action         = $subElement->elementName();

                if ($element->elementName() === 'me') {
                    $this->controllerName = 'MeController';
                    $this->type           = self::CONTEXT_TYPE_ME_SUB_DATA;
                    $this->action         = $this->httpMethod.ucfirst($subElement->elementName());
                    $this->parentModel    = 'User';
                    $this->targetModel    = $this->getModelFromPathElement(0);

                    return;
                }

                if ($element->isPlural() && $subElement->isVariable()) {
                    $this->targetModel = $this->getModelFromPathElement(1);
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

                $this->targetModel    = $this->getModelFromPathElement(1);
                $this->controllerName = ucfirst($element->getModelName()).'Controller';
                $this->action         = $this->httpMethod.ucfirst($subElement->elementName());

                return;
            case 3:
                $parentElement = $this->elements[2];
                $subElement    = $this->elements[1];
                $targetElement = $this->elements[0];

                if ($parentElement->isPlural() && $subElement->isVariable()) {
                    $this->hasParent;
                    $this->parentModel = $this->getModelFromPathElement(2);
                    $this->targetModel = $this->getModelFromPathElement(0);

                    $key                 = snake_case($this->parentModel.'_'.$subElement->variableName());
                    $this->parentFilters = [
                        $key => $subElement->isVariable(),
                    ];

                    switch ($this->httpMethod) {
                        case 'get':
                            if ($targetElement->isPlural()) {
                                $this->type   = self::CONTEXT_TYPE_SHOW;
                                $this->action = 'show';
                            }

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
                $this->controllerName = ucfirst($parentElement->getModelName()).'Controller';
                $this->action         = $this->httpMethod.ucfirst($targetElement->elementName());

                return;
        }

        $targetElement = $this->elements[0];
        $this->action  = $this->httpMethod.ucfirst($targetElement->elementName());
        $this->type    = self::CONTEXT_TYPE_UNKNOWN;
    }
}
