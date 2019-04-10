<?php
namespace LaravelRocket\Generator\Generators\Helpers;

use Illuminate\Support\Str;
use LaravelRocket\Generator\Generators\NameBaseGenerator;
use function ICanBoogie\singularize;

class HelperGenerator extends NameBaseGenerator
{
    protected function canGenerate(): bool
    {
        return !file_exists($this->getPath());
    }

    protected function normalizeName(string $name): string
    {
        if (Str::endsWith(strtolower($name), 'helper')) {
            $name = substr($name, 0, strlen($name) - 7);
        }

        return ucfirst(Str::camel(singularize($name)));
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
        return app_path('Helpers/Production/'.$this->name.'Helper.php');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return 'helper.helper';
    }
}
