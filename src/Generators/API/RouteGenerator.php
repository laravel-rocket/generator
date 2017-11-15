<?php
namespace LaravelRocket\Generator\Generators\API;

use TakaakiMizuno\PhpCodeManipulator\Parser as PhpParser;
use function ICanBoogie\singularize;

class RouteGenerator extends BaseGenerator
{
    protected $ignore = ['List', 'Status'];

    protected $routes = [];

    protected function execute()
    {
        $this->generateRoute();
    }

    protected function generateRoute()
    {
        $routes = [];
        $paths  = $this->document->paths;
        foreach ($paths as $path => $operations) {
            foreach ($operations as $method => $operation) {
                $route = $this->findRoute($path, $method);
                if (empty($route)) {
                    $controllerInfo = $this->decideController($path, $method);
                    if (!array_key_exists($controllerInfo['name'], $routes)) {
                        $routes[$controllerInfo['name']] = [
                            'resource' => [],
                            'others'   => [],
                        ];
                    }
                    if ($controllerInfo['isResource']) {
                        $routes[$controllerInfo['name']]['resource'][] = $route;
                    } else {
                        $routes[$controllerInfo['name']]['others'][] = $route;
                    }
                }
            }
        }

        foreach ($routes as $name => $route) {
            $model = 'Unknown';
            if (preg_match('/^(.+)Controller$/', $name, $matches)) {
                $model = $matches[0];
            }
            $class = $this->getControllerClass($name);
            $path  = $this->convertClassToPath($class);
            if (!file_exists($path)) {
                $stubPath = $this->getStubForController();

                return $this->generateFile($class, $path, $stubPath, [
                    'NAMESPACE' => $this->namespace,
                    'NAME'      => $name,
                    'MODEL'     => $model,
                    'model'     => strtolower($model),
                ]);
            }

            $fileObject = (new PhpParser())->parse($path);
            if (!empty($fileObject)) {
                $classes = &$fileObject->getClasses();
                if (count($classes) > 0) {
                    foreach ($route['resource'] as $action) {
                        $classes[0]->addMethod('', $action['action'], [], ['public'], '');
                    }
                    foreach ($route['others'] as $action) {
                        $classes[0]->addMethod('', $action['action'], [], ['public'], '');
                    }
                    $file = $fileObject->stringify();
                    file_put_contents($path, $file);
                }
            }
            foreach ($route['others'] as $action) {
                $this->addRoute($action['path'], $action['method'], $action['name'], $action['action']);
            }
            $actions = [];
            foreach ($route['resource'] as $action) {
                $actions[] = $action['action'];
            }
            if (count($actions) > 0) {
                $this->addRoute($route['resource'][0]['path'], $route['resource'][0]['method'], $route['resource'][0]['name'], $route['resource'][0]['action'], true, $actions);
            }
        }
    }

    protected function getStubForController()
    {
        return $this->getStubPath('/api/controller.stub');
    }

    protected function getControllerClass($name)
    {
        return '\\App\\Http\\Controller\\'.$this->namespace.'\\'.$name;
    }

    protected function findRoute($path, $method)
    {
        if (starts_with($path, $this->document->basePath)) {
            $path = $this->document->basePath.$path;
        }

        $routeCollection = \Route::getRoutes();
        foreach ($routeCollection as $route) {
            if ($route->uri === $path && in_array(strtoupper($method), $route->methods)) {
                return $route;
            }
        }

        return;
    }

    protected function isValue($fragment)
    {
        if (preg_match('/^{[^}]+}$/', $fragment)) {
            return false;
        }

        return true;
    }

    /**
     * @param string $path
     * @param string $method
     *
     * @return array
     */
    protected function decideController($path, $method)
    {
        if (starts_with($path, '/')) {
            $path = substr($path, 1);
        }
        $fragments  = explode('/', $path);
        $name       = '';
        $action     = '';
        $isResource = false;
        if (count($fragments) === 1 && in_array($method, ['get', 'post'])) {
            $name       = studly_case(singularize($fragments[0])).'Controller';
            $action     = $method === 'get' ? 'index' : 'store';
            $isResource = true;
        } elseif (count($fragments) === 2 && $this->isValue($fragments[1]) && in_array(
            $method,
                ['get', 'put', 'delete']
        )) {
            $name       = studly_case(singularize($fragments[0])).'Controller';
            $action     = $method === 'get' ? 'show' : 'put' ? 'update' : 'destroy';
            $isResource = true;
        } elseif (count($fragments) === 1) {
            $name   = studly_case($fragments[0]).'Controller';
            $action = $method;
        } elseif ($this->isValue($fragments[count($fragments) - 1])) {
            $name       = studly_case(singularize($fragments[count($fragments) - 2])).'Controller';
            $action     = $method === 'get' ? 'show' : 'put' ? 'update' : 'destroy';
            $isResource = true;
        } else {
            $name   = studly_case(singularize($fragments[count($fragments) - 2])).'Controller';
            $action = $fragments[count($fragments) - 1];
        }

        return ['path' => $path, $method => $method, 'name' => $name, 'action' => $action, 'isResource' => $isResource];
    }

    protected function addRoute($path, $method, $controller, $action, $isResource = false, $actions = [])
    {
        $content = file_get_contents($path);
        $postfix = str_replace('\\', '_', $this->namespace);
        $key     = '/* %%ROUTES_'.$postfix.'%% */';

        if (!$isResource) {
            $route = "        Route::$method('$path', '$controller@$action')->name('$path.$action');";
        } else {
            $route = "        Route::resource('$path', '$controller@$action'[".PHP_EOL."                'only' => [".PHP_EOL;
            foreach ($actions as $resourceAction) {
                $route .= "                    '$resourceAction',".PHP_EOL;
            }
            $route .= '                ],'.PHP_EOL.'            ]);';
        }
        if (substr($key, $content) === false) {
            $namespaceFragments = explode('\\', $this->namespace);
            $start              = '';
            $end                = '';
            foreach ($namespaceFragments as $fragment) {
                $lower = strtolower($fragment);
                $start .= "Route::group(['prefix' => '$lower', 'as' => '$lower.', 'namespace' => '$fragment'], function() {".PHP_EOL;
                $end = '        });'.PHP_EOL.$end;
            }
            $this->replaceFile([
                '__EOF__' => $start.PHP_EOL.$route.PHP_EOL.$key.PHP_EOL.$end,
            ], $path);
        } else {
            $this->replaceFile([
                $key => $route,
            ], $path);
        }
    }

    /**
     * @return string
     */
    protected function getRoutesPath()
    {
        $routePath = base_path('/routes/api.php');
        if (!$this->files->exists($routePath)) {
            $route = $this->getStubPath('/api/route_file.stub');
            $this->files->put($routePath, $route);
        }

        return $routePath;
    }
}
