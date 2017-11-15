<?php
namespace LaravelRocket\Generator\Generators\API;

use TakaakiMizuno\SwaggerParser\Objects\V20\Schema;

class ResponseGenerator extends BaseGenerator
{
    protected $ignore = ['List', 'Status'];

    protected function execute()
    {
        $this->generateResponses();
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

    protected function generateResponses()
    {
        $definitions = $this->document->definitions;
        foreach ($definitions as $name => $definition) {
            $this->generateResponse($name, $definition);
        }
    }

    /**
     * @param string $name
     * @param Schema $definition
     *
     * @return bool
     */
    protected function generateResponse($name, $definition)
    {
        $class     = $this->getResponseClass($name);
        $classPath = $this->convertClassToPath($class);

        if (!empty($definition->allOf)) {
            $stubFilePath = $this->getStubForResponse(true);
        } else {
            $stubFilePath = $this->getStubForResponse();
        }

        $columns          = '';
        $columnsFromModel = '';
        foreach ($definition->properties as $name => $property) {
            if (in_array($name, $this->ignore)) {
                continue;
            }
            $ref   = $property->get('$ref');
            $class = '';
            if (!empty($ref)) {
                $default   = 'null';
                $fragments = explode('/', $ref);
                $class     = $fragments[count($fragments) - 1];
            } else {
                switch ($property->type) {
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
                    default:
                        $default = 'null';
                        break;
                }
            }
            if (!empty($columns)) {
                $columns .= PHP_EOL;
                $columnsFromModel .= PHP_EOL;
            }
            $columns .= "        '$name'          => $default,";
            if (!empty($ref)) {
                $columnsFromModel .= "                '$name'          => empty(\$model->$name) ? null : new $class(\$model->$name),";
            } else {
                $columnsFromModel .= "                '$name'          => \$model->$name,";
            }
        }

        if ($this->files->exists($classPath)) {
            return false;
        }

        if (file_exists($classPath)) {
        }

        return $this->generateFile($class, $classPath, $stubFilePath, [
            'COLUMNS'            => $columns,
            'COLUMNS_FROM_MODEL' => $columnsFromModel,
            'NAMESPACE'          => $this->namespace,
            'NAME'               => $name,
        ]);
    }

    /**
     * @param bool $isList
     *
     * @return string
     */
    protected function getStubForResponse($isList = false)
    {
        if ($isList) {
            return $this->getStubPath('/api/response_list.stub');
        }

        return $this->getStubPath('/api/response.stub');
    }
}
