<?php

namespace LaravelRocket\Generator\Generators\Events;

class UnitTestGenerator extends EventGenerator
{
    /**
     * @return string
     */
    protected function getPath(): string
    {
        return base_path('tests/Events/'.$this->name.'Test.php');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return 'event.unittest';
    }
}
