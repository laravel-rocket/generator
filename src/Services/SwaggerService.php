<?php
namespace LaravelRocket\Generator\Services;

use LaravelRocket\Generator\Objects\Swagger\Spec;
use Symfony\Component\Yaml\Yaml;

class SwaggerService
{
    /**
     * @param string $path
     *
     * @return Spec
     */
    public function parse($path)
    {
        $content = file_get_contents($path);
        if ($this->isJson($path)) {
            $data = json_decode($content);
        } else {
            $data = Yaml::parse($content, Yaml::PARSE_OBJECT_FOR_MAP);
        }

        return new Spec($data, $path);
    }

    protected function getPathExtension($path)
    {
        return strtolower(pathinfo($path, PATHINFO_EXTENSION));
    }

    protected function isJson($path)
    {
        $extension = $this->getPathExtension($path);

        return strtolower($extension) === 'json';
    }

    protected function isYaml($path)
    {
        $extension = $this->getPathExtension($path);

        return $extension === 'yml' || $extension === 'yaml';
    }
}
