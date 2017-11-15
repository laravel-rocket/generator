<?php
namespace LaravelRocket\Generator\Generators;

use Symfony\Component\Console\Exception\InvalidArgumentException;
use function ICanBoogie\pluralize;

class AlterMigrationGenerator extends Generator
{
    public function generate($name, $overwrite = false, $baseDirectory = null, $additionalData = [])
    {
        $action = array_get($additionalData, 'action', 'undefined');
        $this->generateMigration($name, $action);
    }

    protected function generateMigration($name, $action)
    {
        $name = $this->getTableName($name);

        if (class_exists($className = $this->getAlterClassName($name, $action))) {
            throw new InvalidArgumentException("A $className migration already exists.");
        }

        $path         = $this->getPath($name, $action);
        $stubFilePath = $this->getStubPath('/migration/alter.stub');

        return $this->generateFile($className, $path, $stubFilePath, [
            'CLASS' => $className,
            'TABLE' => $name,
        ]);
    }

    protected function getTableName($name)
    {
        return pluralize(snake_case($name));
    }

    protected function getAlterClassName($name, $action)
    {
        return 'Alter'.ucfirst(camel_case($name)).ucfirst(camel_case($action)).'Table';
    }

    protected function getPath($name, $action)
    {
        $basePath = database_path('migrations');
        $action   = snake_case($action);

        return $basePath.'/'.date('Y_m_d_His').'_alter_'.$name.'_'.$action.'_table.php';
    }
}
