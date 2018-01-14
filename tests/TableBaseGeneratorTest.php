<?php
namespace LaravelRocket\Generator\Tests;

use LaravelRocket\Generator\Generators\TableBaseGenerator;
use TakaakiMizuno\MWBParser\Parser;

class TableBaseGeneratorTest extends TestCase
{
    public function testGetInstance()
    {
        $generator = new TableBaseGenerator(new \Illuminate\Config\Repository(), new \Illuminate\Filesystem\Filesystem());
        $this->assertNotNull($generator);
    }

    public function testGetRelations()
    {
        $generator = new TableBaseGenerator(new \Illuminate\Config\Repository(), new \Illuminate\Filesystem\Filesystem());
        $parser    = new Parser(__DIR__.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'db.mwb');
        $tables    = $parser->getTables();

        $generator->setTargetTable($tables[0], $tables);
        $relations = $generator->getRelations();

        $this->assertEquals(count($relations), 3);
    }
}
