<?php
namespace LaravelRocket\Generator\Generators\CRUD;

use LaravelRocket\Generator\Generators\TableBaseGenerator;

class CRUDBaseGenerator extends TableBaseGenerator
{
    protected function canGenerate(): bool
    {
        return !$this->detectRelationTable($this->table);
    }
}
