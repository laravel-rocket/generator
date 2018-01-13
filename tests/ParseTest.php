<?php
namespace LaravelRocket\Generator\Tests;

use PhpParser\Lexer;
use PhpParser\ParserFactory;

class ParseTest extends TestCase
{
    public function testGetInstance()
    {
        $lexer = new Lexer([
            'usedAttributes' => [
                'comments', 'startLine', 'endLine',
            ],
        ]);

        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7, $lexer);

        $statements = $parser->parse(file_get_contents(__FILE__));
        print_r($statements);

        $this->assertTrue(true);
    }
}
