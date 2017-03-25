<?php

namespace LaravelRocket\Generator\Console\Commands;

use LaravelRocket\Generator\Generators\ModelGenerator;

class ModelGeneratorCommand extends GeneratorCommand
{

    protected $name        = 'make:model';

    protected $description = 'Create a new model class';

    protected $generator   = ModelGenerator::class;

}
