<?php
namespace LaravelRocket\Generator\Tests;

use LaravelRocket\Generator\Validators\Table\TableSchemaValidator;
use TakaakiMizuno\MWBParser\Parser;

class TableSchemaValidatorTest extends TestCase
{
    public function testGetInstance()
    {
        $validator = new TableSchemaValidator(new \Illuminate\Config\Repository(), new \Illuminate\Filesystem\Filesystem());
        $this->assertNotNull($validator);
    }

    public function testGetRelations()
    {
        $validator = new TableSchemaValidator(new \Illuminate\Config\Repository(), new \Illuminate\Filesystem\Filesystem());
        $parser    = new Parser(__DIR__.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'db.mwb');
        $tables    = $parser->getTables();

        list($success, $errors) = $validator->validate($tables, null);
        foreach ($errors as $error) {
            if (!empty($error)) {
                print $error->getMessage().'/'.$error->getTarget().PHP_EOL;
            }
        }
        $this->assertEquals(true, $success);
    }
}
