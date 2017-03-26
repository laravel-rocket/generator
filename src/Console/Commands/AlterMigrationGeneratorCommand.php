<?php

namespace LaravelRocket\Generator\Console\Commands;

use LaravelRocket\Generator\Generators\CreateMigrationGenerator;

class AlterMigrationGeneratorCommand extends GeneratorCommand
{

    protected $name        = 'rocket:make:migration:alter';

    protected $description = 'Create a migration for create table';

    protected $generator   = CreateMigrationGenerator::class;

}
