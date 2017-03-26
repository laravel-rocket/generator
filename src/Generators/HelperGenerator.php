<?php

namespace LaravelRocket\Generator\Generators;

class HelperGenerator extends Generator
{

    public function generate($name, $overwrite = false, $baseDirectory = null)
    {
        $helperName = $this->getName($name);
        $this->generateHelper($helperName);
        $this->generateHelperInterface($helperName);
        $this->generateFacade($helperName);
        $this->generateHelperUnitTest($helperName);
        $this->bindInterface($helperName);
        $this->addFacadeToConfig($helperName);
    }

    /**
     * @param  string $name
     * @return string
     */
    protected function getName($name)
    {
        $className = $this->getClassName($name);
        $rootName = str_replace('Helper', '', $className);

        return $rootName;
    }

    protected function getHelperClass($name)
    {
        $helperName = $this->getName($name);

        return '\\App\\Helpers\\Production\\'.$helperName;
    }

    /**
     * @param  string $helperName
     * @return bool
     */
    protected function generateHelper($helperName)
    {
        $className = $this->getHelperClass($helperName);
        $classPath = $this->convertClassToPath($className);

        $stubFilePath = __DIR__.'/../../stubs/helper/helper.stub';

        return $this->generateFile($className, $classPath, $stubFilePath);
    }

    /**
     * @param  string $helperName
     * @return bool
     */
    protected function generateHelperInterface($helperName)
    {
        $className = '\\App\\Helpers\\'.$helperName.'Interface';
        $classPath = $this->convertClassToPath($className);

        $stubFilePath = __DIR__.'/../../stubs/helper/helper_interface.stub';

        return $this->generateFile($className, $classPath, $stubFilePath);
    }

    /**
     * @param  string $helperName
     * @return bool
     */
    protected function generateFacade($helperName)
    {
        $className = '\\App\\Facades\\'.$helperName;
        $classPath = $this->convertClassToPath($className);

        $stubFilePath = __DIR__.'/../../stubs/helper/facade.stub';

        return $this->generateFile($className, $classPath, $stubFilePath);
    }

    /**
     * @param  string $helperName
     * @return bool
     */
    protected function generateHelperUnitTest($helperName)
    {
        $classPath = base_path('/../tests/Helpers/'.$helperName.'Test.php');
        $stubFilePath = __DIR__.'/../../stubs/helper/helper_unittest.stub';

        return $this->generateFile($helperName, $classPath, $stubFilePath);
    }

    /**
     * @param string $helperName
     *
     * @return bool
     */
    protected function bindInterface($helperName)
    {
        $bindingPath = base_path('/Providers/HelperServiceProvider.php');

        $key = '/* NEW BINDING */';
        $bind = '$this->app->singleton('.PHP_EOL."            \\App\\Helpers\\".$helperName."Interface::class,".PHP_EOL."            \\App\\Helpers\\Production\\".$helperName."::class".PHP_EOL.'        );'.PHP_EOL.PHP_EOL.'        ';
        $this->replaceFile([
            $key => $bind,
        ], $bindingPath);

        return true;
    }

    protected function addFacadeToConfig($helperName)
    {
        $appConfigPath = base_path('/../config/app.php');
        $key = '/* NEW FACADE */';
        $facade = "'".$helperName."'  => \\App\\Facades\\".$helperName.'::class,'.PHP_EOL.'        '.$key;

        $this->replaceFile([
            $key => $facade,
        ], $appConfigPath);

        return true;
    }
}
