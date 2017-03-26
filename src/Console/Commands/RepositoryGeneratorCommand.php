<?php

namespace LaravelRocket\Generator\Console\Commands;

use LaravelRocket\Generator\Generators\RepositoryGenerator;

class RepositoryGeneratorCommand extends GeneratorCommand
{

    protected $name        = 'rocket:make:repository';

    protected $description = 'Create a new repository class';

    protected $generator   = RepositoryGenerator::class;

}
