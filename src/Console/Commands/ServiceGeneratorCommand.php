<?php

namespace LaravelRocket\Generator\Console\Commands;

use LaravelRocket\Generator\Generators\ServiceGenerator;

class ServiceGeneratorCommand extends GeneratorCommand
{

    protected $name        = 'make:service';

    protected $description = 'Create a new service class';

    protected $generator   = ServiceGenerator::class;

}
