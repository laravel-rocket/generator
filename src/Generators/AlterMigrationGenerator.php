<?php

namespace LaravelRocket\Generator\Generators;

use Symfony\Component\Console\Exception\InvalidArgumentException;

class AlterMigrationGenerator extends Generator
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

        $path = $this->getPath($name);
        $stubFilePath = $this->getStubPath('/migration/alter.stub');

        return $this->generateFile($className, $path, $stubFilePath, [
            'CLASS' => $className,
            'TABLE' => $name,
        ]);
    }

    protected function getTableName($name)
    {
        return \StringHelper::pluralize(\StringHelper::camel2Snake($name));
    }

    protected function getClassName($name)
    {
        return 'Alter'. ucfirst(\StringHelper::snake2Camel($name)).'Table';
    }

    protected function getPath($name)
    {
        $basePath = database_path('migrations');

        return $basePath.'/'.date('Y_m_d_His').'_create_'.$name.'_table.php';
    }
}
