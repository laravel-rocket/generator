<?php
namespace LaravelRocket\Generator\Console\Commands;

use LaravelRocket\Generator\Generators\AlterMigrationGenerator;

class AlterMigrationGeneratorCommand extends GeneratorCommand
{
    protected $name        = 'rocket:make:migration:alter';

    protected $description = 'Create a migration for alter table';

    protected $generator   = AlterMigrationGenerator::class;
}
