<?php

namespace LaravelRocket\Generator\Console\Commands;

use LaravelRocket\Generator\Generators\CreateMigrationGenerator;

class CreateMigrationGeneratorCommand extends GeneratorCommand
{

    protected $name        = 'rocket:make:migration:create';

    protected $description = 'Create a migration for create table';

    protected $generator   = CreateMigrationGenerator::class;

}
