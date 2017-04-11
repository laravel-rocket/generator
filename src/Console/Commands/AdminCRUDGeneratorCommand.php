<?php
namespace LaravelRocket\Generator\Console\Commands;

use LaravelRocket\Generator\Generators\AdminCRUDGenerator;

class AdminCRUDGeneratorCommand extends GeneratorCommand
{
    protected $name        = 'rocket:make:admin:crud';

    protected $description = 'Create a admin crud for database table';

    protected $generator   = AdminCRUDGenerator::class;
}
