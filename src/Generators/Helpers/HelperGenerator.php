<?php
namespace LaravelRocket\Generator\Generators\Helpers;

use LaravelRocket\Generator\Generators\NameBaseGenerator;
use function ICanBoogie\singularize;

class HelperGenerator extends NameBaseGenerator
{
    protected function normalizeName(string $name): string
    {
        if (ends_with(strtolower($name), 'helper')) {
            $name = substr($name, 0, strlen($name) - 7);
        }

        return ucfirst(camel_case(singularize($name)));
    }

    /**
     * @return array
     */
    protected function getVariables(): array
    {
        $helperName   = $this->name.'Helper';
        $repositories = [];

        foreach ($this->getRepositories() as $repository) {
            if (strpos($repository, $this->name) !== false) {
                $repositories[] = $repository;
            }
        }
        $variables = [
            'helperName'    => $helperName,
            'repositories'  => $repositories,
        ];

        return $variables;
    }

    /**
     * @return string
     */
    protected function getPath(): string
    {
        return app_path('/Helpers/Production/'.$this->name.'Helper.php');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return 'helper.helper';
    }
}
