<?php
namespace LaravelRocket\Generator\Generators;

use LaravelRocket\Generator\Objects\Swagger\Definition;
use LaravelRocket\Generator\Objects\Swagger\Spec;
use LaravelRocket\Generator\Services\SwaggerService;

class APIGenerator extends Generator
{
    /** @var Spec */
    protected $swagger;

    protected $namespace;

    public function generate($swaggerPath, $overwrite = false, $baseDirectory = null)
    {
        $ret = $this->readSwaggerFile($swaggerPath);
        if (!$ret) {
            return;
        }
        $this->generateRoute();
        $this->generateResponses();
    }

    protected function generateRoute()
    {
        $routesPath = $this->getRoutesPath();
        if (!$this->files->exists($routesPath)) {
            $route = $this->getStubPath('/api/route_file.stub');
            $this->files->put($routesPath, $route);
        }
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function getResponseClass($name)
    {
        return '\\App\\Http\\Responses\\'.$this->namespace.'\\'.$name;
    }

    /**
     * @return string
     */
    protected function getRoutesPath()
    {
        return base_path('/routes/api.php');
    }

    protected function generateResponses()
    {
        $definitions = $this->swagger->getDefinitions();
        foreach ($definitions as $definition) {
            $this->generateResponse($definition);
        }
    }

    /**
     * @param Definition $definition
     *
     * @return bool
     */
    protected function generateResponse($definition)
    {
        $name      = $definition->getName();
        $class     = $this->getResponseClass($name);
        $classPath = $this->convertClassToPath($class);

        $stubFilePath = $this->getStubForResponse();

        $columns          = '';
        $columnsFromModel = '';
        foreach ($definition->getProperties() as $property) {
            $default = 'null';
            switch ($property->getType()) {
                case 'string':
                    $default = '\'\'';
                    break;
                case 'integer':
                case 'int':
                    $default = '0';
                    break;
                case 'array':
                    $default = '[]';
                    break;
            }
            if (!empty($columns)) {
                $columns .= PHP_EOL;
                $columnsFromModel .= PHP_EOL;
            }
            $columns .= '        \''.$property->getName().'\'          => '.$default.',';
            $columnsFromModel .= '                \''.$property->getName().'\'          => $model->'.$property->getName().',';
        }

        if( $this->files->exists($classPath) ) {
            return false;
        }

        return $this->generateFile($class, $classPath, $stubFilePath, [
            'COLUMNS'            => $columns,
            'COLUMNS_FROM_MODEL' => $columnsFromModel,
            'NAMESPACE'          => $this->namespace,
            'NAME'               => $name,
        ]);
    }

    /**
     * @return string
     */
    protected function getStubForResponse()
    {
        return $this->getStubPath('/api/response.stub');
    }

    protected function readSwaggerFile($swaggerPath)
    {
        $service       = new SwaggerService();
        $this->swagger = $service->parse($swaggerPath);
        if (empty($this->swagger)) {
            $this->error('Fail to parse Swagger File');
        }

        $this->namespace = $this->swagger->getNamespace();

        return true;
    }
}
