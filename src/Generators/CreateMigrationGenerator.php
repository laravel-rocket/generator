<?php
namespace LaravelRocket\Generator\Generators;

use Symfony\Component\Console\Exception\InvalidArgumentException;
use function ICanBoogie\pluralize;

class CreateMigrationGenerator extends Generator
{
    public function generate($name, $overwrite = false, $baseDirectory = null)
    {
        $this->generateMigration($name);
    }

    protected function generateMigration($name)
    {
        $name = $this->getTableName($name);

        if (class_exists($className = $this->getClassName($name))) {
            throw new InvalidArgumentException("A $className migration already exists.");
        }

        $path         = $this->getPath($name);
        $stubFilePath = $this->getStubPath('/migration/create.stub');

        return $this->generateFile($className, $path, $stubFilePath, [
            'CLASS' => $className,
            'TABLE' => $name,
        ]);
    }

    protected function getTableName($name)
    {
        return pluralize(snake_case($name));
    }

    protected function getClassName($name)
    {
        return 'Create'.ucfirst(camel_case($name)).'Table';
    }

    protected function getPath($name)
    {
        $basePath = database_path('migrations');

        return $basePath.'/'.date('Y_m_d_His').'_create_'.$name.'_table.php';
    }
}
