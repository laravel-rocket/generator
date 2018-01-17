<?php
namespace LaravelRocket\Generator\Generators;

use function ICanBoogie\singularize;

class NameBaseGenerator extends BaseGenerator
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @param string $name
     *
     * @return bool
     */
    public function generate($name): bool
    {
        $this->name = $this->normalizeName($name);

        if (!$this->canGenerate()) {
            return false;
        }

        $view      = $this->getView();
        $variables = $this->getVariables();

        $path = $this->getPath();
        if (file_exists($path)) {
            unlink($path);
        }
        $this->fileService->render($view, $path, $variables, true, true);

        return true;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function normalizeName(string $name): string
    {
        return ucfirst(camel_case(singularize($name)));
    }

    /**
     * @return bool
     */
    protected function canGenerate(): bool
    {
        return true;
    }

    /**
     * @return string
     */
    protected function getPath(): string
    {
        return '';
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return '';
    }

    /**
     * @return array
     */
    protected function getVariables(): array
    {
        return [];
    }

    /**
     * @return array
     */
    protected function getRepositories(): array
    {
        $repositories = [];

        $path  = app_path('Repositories');
        $files = scandir($path);
        foreach ($files as $file) {
            if (preg_match('/^(.+)RepositoryInterface.php$/', $file, $matches)) {
                $repositories[] = $matches[1].'Repository';
            }
        }

        return $repositories;
    }
}
