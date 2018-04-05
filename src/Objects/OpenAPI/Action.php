<?php
namespace LaravelRocket\Generator\Objects\OpenAPI;

use function ICanBoogie\singularize;

class Action
{
    /** @var string */
    protected $path;

    /** @var string */
    protected $method;

    protected $data;

    /** @var \LaravelRocket\Generator\Objects\OpenAPI\PathElement[] $elements */
    protected $elements;

    /** @var string */
    protected $controller;

    /** @var string[] */
    protected $params = [];

    /**
     * @param \LaravelRocket\Generator\Objects\OpenAPI\PathElement[] $elements
     * @param string                                                 $httpMethod
     *
     * @return array
     */
    public static function getAllCandidates($elements, $httpMethod, $path, $data)
    {
        $httpMethod = strtolower($httpMethod);
        $actions    = [];
        $elements   = array_reverse($elements);

        // GET/POST /users
        if ($elements[0]->isPlural()) {
            $controller = title_case(snake_case(singularize($elements[0]->elementName())));
            switch ($httpMethod) {
                case 'get':
                    $method    = 'index';
                    $actions[] = new static($controller, $method, $path, $data);
                    break;
                case 'post':
                    $method    = 'store';
                    $actions[] = new static($controller, $method, $path, $data);
                    break;
            }
        }

        // GET/PUT/DELETE /users/{id}
        if (count($elements) >= 2 && $elements[0]->isVariable() && $elements[1]->isPlural()) {
            $controller = title_case(snake_case(singularize($elements[0]->elementName())));
            switch ($httpMethod) {
                case 'get':
                    $method    = 'show';
                    $actions[] = new static($controller, $method, $path, $data, [$elements[0]->variableName()]);
                    break;
                case 'put':
                case 'patch':
                    $method    = 'update';
                    $actions[] = new static($controller, $method, $path, $data, [$elements[0]->variableName()]);
                    break;
                case 'delete':
                    $method    = 'destroy';
                    $actions[] = new static($controller, $method, $path, $data, [$elements[0]->variableName()]);
                    break;
            }
        }

        // GET/POST/PUT/DELETE /users/info
        if (count($elements) >= 2 && !$elements[0]->isVariable() && $elements[1]->isPlural()) {
            $controller = title_case(snake_case(singularize($elements[0]->elementName())));
            switch ($httpMethod) {
                case 'get':
                    $method    = 'get'.ucfirst(camel_case($elements[0]->elementName()));
                    $actions[] = new static($controller, $method, $path, $data);
                    break;
                case 'post':
                    $method    = 'post'.ucfirst(camel_case($elements[0]->elementName()));
                    $actions[] = new static($controller, $method, $path, $data);
                    break;
                case 'put':
                case 'patch':
                    $method    = 'put'.ucfirst(camel_case($elements[0]->elementName()));
                    $actions[] = new static($controller, $method, $path, $data);
                    break;
                case 'delete':
                    $method    = 'delete'.ucfirst(camel_case($elements[0]->elementName()));
                    $actions[] = new static($controller, $method, $path, $data);
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
                    $actions[] = new static($controllerOne, $method, $path, $data, [$elements[1]->variableName()]);
                    $method    = 'get'.ucfirst(camel_case($elements[0]->elementName()));
                    $actions[] = new static($controllerTwo, $method, $path, $data);
                    break;
                case 'post':
                    $method    = 'create';
                    $actions[] = new static($controllerOne, $method, $path, $data, [$elements[1]->variableName()]);
                    $method    = 'post'.ucfirst(camel_case($elements[0]->elementName()));
                    $actions[] = new static($controllerTwo, $method, $path, $data);
                    break;
                case 'put':
                case 'patch':
                    $method    = 'put'.ucfirst(camel_case($elements[0]->elementName()));
                    $actions[] = new static($controllerTwo, $method, $path, $data, [$elements[1]->variableName()]);
                    break;
                case 'delete':
                    $method    = 'delete'.ucfirst(camel_case($elements[0]->elementName()));
                    $actions[] = new static($controllerTwo, $method, $path, $data, [$elements[1]->variableName()]);
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
                    $actions[] = new static($controllerOne, $method, $path, $data, [$elements[2]->variableName(), $elements[0]->variableName()]);
                    break;
                case 'put':
                case 'patch':
                    $method    = 'update';
                    $actions[] = new static($controllerOne, $method, $path, $data, [$elements[2]->variableName(), $elements[0]->variableName()]);
                    break;
                case 'delete':
                    $method    = 'destroy';
                    $actions[] = new static($controllerOne, $method, $path, $data, [$elements[2]->variableName(), $elements[0]->variableName()]);
                    break;
            }
        }

        return $actions;
    }

    /**
     * Action constructor.
     *
     * @param string   $controller
     * @param string   $method
     * @param string[] $params
     * @param string   $path
     * @param array    $data
     */
    public function __construct($controller, $method, $path, $data, $params = [])
    {
        $this->controller = $controller;
        $this->method     = $method;
        $this->path       = $path;
        $this->data       = $data;

        $this->setParams($params);
    }

    /**
     * @return string
     */
    public function getController()
    {
        return $this->controller;
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

    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    protected function setParams($params)
    {
        $this->params = [];
        foreach ($params as $param) {
            $this->params[] = '$'.$param;
        }
    }
}
