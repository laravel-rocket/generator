<?php
namespace LaravelRocket\Generator\Console\Commands;

use LaravelRocket\Generator\Generators\HelperGenerator;

class HelperGeneratorCommand extends GeneratorCommand
{
    protected $name        = 'rocket:make:helper';

    protected $description = 'Create a new helper class';

    protected $generator   = HelperGenerator::class;
}
