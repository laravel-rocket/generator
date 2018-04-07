<?php
namespace LaravelRocket\Generator\Objects\OpenAPI;

use function ICanBoogie\singularize;

class Action
{
    /** @var string */
    protected $path;

    /** @var string */
    protected $method;

    /** @var \TakaakiMizuno\SwaggerParser\Objects\Base */
    protected $info;

    /** @var \LaravelRocket\Generator\Objects\OpenAPI\PathElement[] $elements */
    protected $elements;

    /** @var string */
    protected $controllerName;

    /** @var string[] */
    protected $params = [];

    /** @var bool $usePagination */
    protected $usePagination = false;

    /** @var string $requestName */
    protected $requestName = '';

    /** @var \LaravelRocket\Generator\Objects\OpenAPI\OpenAPISpec */
    protected $spec;

    /** @var \LaravelRocket\Generator\Objects\OpenAPI\Definition|null */
    protected $response;

    /** @var \LaravelRocket\Generator\Objects\OpenAPI\Request */
    protected $request;

    /** @var string */
    protected $repositoryName;

    /**
     * @param \LaravelRocket\Generator\Objects\OpenAPI\PathElement[] $elements
     * @param string                                                 $httpMethod
     * @param string                                                 $path
     * @param \TakaakiMizuno\SwaggerParser\Objects\Base              $info
     * @param \LaravelRocket\Generator\Objects\OpenAPI\OpenAPISpec   $spec
     *
     * @return array
     */
    public static function getAllCandidates($elements, $httpMethod, $path, $info, $spec)
    {
        $httpMethod = strtolower($httpMethod);
        $actions    = [];
        $elements   = array_reverse($elements);

        $params = [];
        foreach ($elements as $element) {
            if ($element->isVariable()) {
                $params[] = $element->variableName();
            }
            $params = array_reverse($params);
        }

        // GET/POST /users
        if ($elements[0]->isPlural()) {
            $controller = title_case(snake_case(singularize($elements[0]->elementName())));
            switch ($httpMethod) {
                case 'get':
                    $method    = 'index';
                    $actions[] = new static($controller, $method, $path, $info, $params, $spec);
                    break;
                case 'post':
                    $method    = 'store';
                    $actions[] = new static($controller, $method, $path, $info, $params, $spec);
                    break;
            }
        }

        // GET/PUT/DELETE /users/{id}
        if (count($elements) >= 2 && $elements[0]->isVariable() && $elements[1]->isPlural()) {
            $controller = title_case(snake_case(singularize($elements[0]->elementName())));
            switch ($httpMethod) {
                case 'get':
                    $method    = 'show';
                    $actions[] = new static($controller, $method, $path, $info, $params, $spec);
                    break;
                case 'put':
                case 'patch':
                    $method    = 'update';
                    $actions[] = new static($controller, $method, $path, $info, $params, $spec);
                    break;
                case 'delete':
                    $method    = 'destroy';
                    $actions[] = new static($controller, $method, $path, $info, $params, $spec);
                    break;
            }
        }

        // GET/POST/PUT/DELETE /users/info
        if (count($elements) >= 2 && !$elements[0]->isVariable() && $elements[1]->isPlural()) {
            $controller = title_case(snake_case(singularize($elements[0]->elementName())));
            switch ($httpMethod) {
                case 'get':
                    $method    = 'get'.ucfirst(camel_case($elements[0]->elementName()));
                    $actions[] = new static($controller, $method, $path, $info, $params, $spec);
                    break;
                case 'post':
                    $method    = 'post'.ucfirst(camel_case($elements[0]->elementName()));
                    $actions[] = new static($controller, $method, $path, $info, $params, $spec);
                    break;
                case 'put':
                case 'patch':
                    $method    = 'put'.ucfirst(camel_case($elements[0]->elementName()));
                    $actions[] = new static($controller, $method, $path, $info, $params, $spec);
                    break;
                case 'delete':
                    $method    = 'delete'.ucfirst(camel_case($elements[0]->elementName()));
                    $actions[] = new static($controller, $method, $path, $info, $params, $spec);
                    break;
            }
        }

        // GET/POST/PUT/DELETE /users/{id}/friends => UserFriendController
        if (count($elements) > 3 && $elements[0]->isPlural() &&
            $elements[1]->isVariable() && $elements[2]->isPlural()) {
            $controllerOne = title_case(snake_case(singularize($elements[2]->elementName()))).
                title_case(snake_case(singularize($elements[0]->elementName())));
            $controllerTwo = title_case(snake_case(singularize($elements[2]->elementName())));
            switch ($httpMethod) {
                case 'get':
                    $method    = 'index';
                    $actions[] = new static($controllerOne, $method, $path, $info, $params, $spec);
                    $method    = 'get'.ucfirst(camel_case($elements[0]->elementName()));
                    $actions[] = new static($controllerTwo, $method, $path, $info, $params, $spec);
                    break;
                case 'post':
                    $method    = 'create';
                    $actions[] = new static($controllerOne, $method, $path, $info, $params, $spec);
                    $method    = 'post'.ucfirst(camel_case($elements[0]->elementName()));
                    $actions[] = new static($controllerTwo, $method, $path, $info, $params, $spec);
                    break;
                case 'put':
                case 'patch':
                    $method    = 'put'.ucfirst(camel_case($elements[0]->elementName()));
                    $actions[] = new static($controllerTwo, $method, $path, $info, $params, $spec);
                    break;
                case 'delete':
                    $method    = 'delete'.ucfirst(camel_case($elements[0]->elementName()));
                    $actions[] = new static($controllerTwo, $method, $path, $info, $params, $spec);
                    break;
            }
        }

        // GET/PUT/DELETE /users/{userId}/friends/{friendId} => UserFriendController
        if (count($elements) > 4 && $elements[0]->isVariable() && $elements[1]->isPlural() &&
            $elements[2]->isVariable() && $elements[3]->isPlural()) {
            $controllerOne = title_case(snake_case(singularize($elements[3]->elementName()))).
                title_case(snake_case(singularize($elements[1]->elementName())));
            switch ($httpMethod) {
                case 'get':
                    $method    = 'show';
                    $actions[] = new static($controllerOne, $method, $path, $info, $params, $spec);
                    break;
                case 'put':
                case 'patch':
                    $method    = 'update';
                    $actions[] = new static($controllerOne, $method, $path, $info, $params, $spec);
                    break;
                case 'delete':
                    $method    = 'destroy';
                    $actions[] = new static($controllerOne, $method, $path, $info, $params, $spec);
                    break;
            }
        }

        return $actions;
    }

    /**
     * Action constructor.
     *
     * @param string                                               $controllerName
     * @param string                                               $method
     * @param string                                               $path
     * @param \TakaakiMizuno\SwaggerParser\Objects\Base            $info
     * @param string[]                                             $params
     * @param \LaravelRocket\Generator\Objects\OpenAPI\OpenAPISpec $spec
     */
    public function __construct($controllerName, $method, $path, $info, $params = [], $spec)
    {
        $this->controllerName = $controllerName;
        $this->method         = $method;
        $this->path           = $path;
        $this->info           = $info;
        $this->spec           = $spec;

        $this->setParams($params);
        $this->setRepository();
        $this->setRequest();
    }

    /**
     * @return string
     */
    public function getControllerName()
    {
        return $this->controllerName;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string[]
     */
    public function getParams()
    {
        return $this->params;
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
    public function getRepositoryName()
    {
        return $this->repositoryName;
    }

    /**
     * @param array $params
     */
    protected function setParams($params)
    {
        $this->params = [];
        foreach ($params as $param) {
            $this->params[] = '$'.$param;
        }
    }

    protected function setRepository()
    {
        $responses = $this->info->responses;
        foreach ($responses as $statusCode => $response) {
            if (substr($statusCode, 0, 1) === '2') {
                $ref            = $response->{$ref};
                $this->response = $this->spec->findDefinition($ref);
                if ($this->response->getType() === Definition::TYPE_MODEL) {
                    $model                = $this->response->getModelName();
                    $this->repositoryName = $model.'Repository';
                }

                return;
            }
        }

        $this->response = null;
    }

    protected function setRequest()
    {
        $this->request = new Request($this->method, $this->info, $this->response, $this->spec);
    }
}
