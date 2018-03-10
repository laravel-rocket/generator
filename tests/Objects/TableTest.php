<?php

namespace LaravelRocket\Generator\Tests\Objects;

use LaravelRocket\Generator\Objects\Table;
use LaravelRocket\Generator\Tests\TestCase;
use TakaakiMizuno\MWBParser\Parser;

class TableTest extends TestCase
{

    protected function getTables()
    {
        $path = [
            __DIR__,
            '..',
            'data',
            'db.mwb',
        ];

        $parser = new Parser(implode(DIRECTORY_SEPARATOR, $path));
        $tables = $parser->getTables();

        return $tables;
    }

    protected function getTable($name, $tables){
        foreach( $tables as $table){
            if( $table->getName() === $name){
                return $table;
            }
        }

        return null;
    }

    public function testGetInstance()
    {
        $tables = $this->getTables();
        $table = $this->getTable('users', $tables);

        $tableObject = new Table($table, $tables);
        $this->assertNotEmpty($tableObject);
    }

    public function testHasColumn()
    {
        $tables = $this->getTables();
        $table = $this->getTable('users', $tables);

        $tableObject = new Table($table, $tables);
        $this->assertTrue($tableObject->hasColumn('id'));
    }

    public function testHasRelation()
    {
        $tables = $this->getTables();
        $table = $this->getTable('users', $tables);

        $tableObject = new Table($table, $tables);

        $this->assertTrue($tableObject->hasRelation('branches'));
    }

    public function testCheckRelationTable()
    {
        $tables = $this->getTables();
        $table = $this->getTable('branch_users', $tables);

        $tableObject = new Table($table, $tables);
        $this->assertTrue($tableObject->isRelationTable());
    }

    public function testCheckAuthTable()
    {
        $tables = $this->getTables();
        $table = $this->getTable('users', $tables);

        $tableObject = new Table($table, $tables);
        $this->assertTrue($tableObject->hasRelation('branches'));
    }

    public function testColumnHasRelation()
    {
        $tables = $this->getTables();
        $table = $this->getTable('branch_users', $tables);

        $tableObject = new Table($table, $tables);
        $column = $tableObject->getColumn('user_id');

        $this->assertTrue($column->hasRelation());
    }

}
