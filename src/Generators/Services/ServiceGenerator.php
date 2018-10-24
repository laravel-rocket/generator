<?php
namespace LaravelRocket\Generator\Generators\Services;

use LaravelRocket\Generator\Generators\NameBaseGenerator;
use function ICanBoogie\singularize;

class ServiceGenerator extends NameBaseGenerator
{
    protected function canGenerate(): bool
    {
        return !file_exists($this->getPath());
    }

    protected function normalizeName(string $name): string
    {
        if (ends_with(strtolower($name), 'service')) {
            $name = substr($name, 0, strlen($name) - 7);
        }

        return ucfirst(camel_case(singularize($name)));
    }

    /**
     * @return array
     */
    protected function getVariables(): array
    {
        $serviceName  = $this->name.'Service';
        $repositories = [];

        foreach ($this->getRepositories() as $repository) {
            if (strpos($repository, $this->name) !== false) {
                $repositories[] = $repository;
            }
        }
        $variables = [
            'serviceName'   => $serviceName,
            'repositories'  => $repositories,
            'isAuthService' => $this->isAuthService(),
        ];

        return $variables;
    }

    /**
     * @return string
     */
    protected function getPath(): string
    {
        return app_path('Services/Production/'.$this->name.'Service.php');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return 'service.service';
    }

    /**
     * @return bool
     */
    protected function isAuthService(): bool
    {
        $modelPath = app_path('Models'.DIRECTORY_SEPARATOR.$this->name.'.php');
        if (!file_exists($modelPath)) {
            return false;
        }

        $contents = file_get_contents($modelPath);
        if (strpos($contents, 'extends AuthenticatableBase') === false) {
            return false;
        }

        return true;
    }
}
