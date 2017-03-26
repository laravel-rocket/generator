<?php

namespace LaravelRocket\Generator\Generators;

class RepositoryGenerator extends Generator
{

    public function generate($name, $overwrite = false, $baseDirectory = null)
    {
        $modelName = $this->getModelName($name);
        $this->generateRepository($modelName);
        $this->generateRepositoryInterface($modelName);
        $this->generateRepositoryUnitTest($modelName);
        $this->bindInterface($modelName);
    }

    /**
     * @param  string $name
     * @return string
     */
    protected function getModelName($name)
    {
        $className = $this->getClassName($name);
        $modelName = str_replace('Repository', '', $className);

        return $modelName;
    }

    /**
     * @param  string $name
     * @return string
     */
    protected function getModelClass($name)
    {
        $modelName = $this->getModelName($name);

        return '\\App\\Models\\'.$modelName;
    }

    protected function getRepositoryClass($name)
    {
        $modelName = $this->getModelName($name);

        return '\\App\\Repositories\\Eloquent\\'.$modelName.'Repository';
    }

    /**
     * @param  string $modelName
     * @return bool
     */
    protected function generateRepository($modelName)
    {
        $className = $this->getRepositoryClass($modelName);
        $classPath = $this->convertClassToPath($className);
        $modelClass = $this->getModelClass($modelName);
        $instance = new $modelClass();

        $stubFilePath = __DIR__.'/../../stubs/repository/repository.stub';
        if ($instance instanceof \LaravelRocket\Foundation\Models\AuthenticatableBase) {
            $stubFilePath = __DIR__.'/../../stubs/repository/repository.stub';
        }

        return $this->generateFile($modelName, $classPath, $stubFilePath);
    }

    /**
     * @param  string $modelName
     * @return bool
     */
    protected function generateRepositoryInterface($modelName)
    {
        $className = '\\App\\Repositories\\'.$modelName.'RepositoryInterface';
        $classPath = $this->convertClassToPath($className);
        $modelClass = $this->getModelClass($modelName);
        $instance = new $modelClass();

        $stubFilePath = __DIR__.'/../../stubs/repository/repository_interface.stub';
        if ($instance instanceof \LaravelRocket\Foundation\Models\AuthenticatableBase) {
            $stubFilePath = __DIR__.'/../../stubs/repository/repository_interface.stub';
        }

        return $this->generateFile($modelName, $classPath, $stubFilePath);
    }

    /**
     * @param  string $modelName
     * @return bool
     */
    protected function generateRepositoryUnitTest($modelName)
    {
        $classPath = base_path('/tests/Repositories/'.$modelName.'RepositoryTest.php');
        $modelClass = $this->getModelClass($modelName);
        $instance = new $modelClass();

        $stubFilePath = __DIR__.'/../../stubs/repository/repository_unittest.stub';
        if ($instance instanceof \LaravelRocket\Foundation\Models\AuthenticatableBase) {
            $stubFilePath = __DIR__.'/../../stubs/repository/repository_unittest.stub';
        }

        return $this->generateFile($modelName, $classPath, $stubFilePath);
    }

    /**
     * @param  string $name
     * @return bool
     */
    protected function bindInterface($name)
    {
        $bindingPath = base_path('/app/Providers/RepositoryServiceProvider.php');

        $key = '/* NEW BINDING */';
        $bind = '$this->app->singleton('.PHP_EOL."            \\App\\Repositories\\".$name."RepositoryInterface::class,"
            .PHP_EOL."            \\App\\Repositories\\Eloquent\\".$name."Repository::class".PHP_EOL.'        );'
            .PHP_EOL.PHP_EOL.'        ';
        $this->replaceFile([
            $key => $bind,
        ], $bindingPath);

        return true;
    }
}
