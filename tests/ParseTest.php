<?php
namespace LaravelRocket\Generator\Tests;

use PhpParser\Lexer;
use PhpParser\ParserFactory;

class ParseTest extends TestCase
{
    const TEXT_BBB = 'saaatring';
    const TEXT_AAA = 1;

    public function testGetInstance()
    {
        $lexer = new Lexer([
            'usedAttributes' => [
                'comments', 'startLine', 'endLine',
            ],
        ]);

        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7, $lexer);

        $statements = $parser->parse(file_get_contents(__FILE__));
        $const      = $this->getConst($statements);

        $prettyPrinter = new \PhpParser\PrettyPrinter\Standard;
        print $prettyPrinter->prettyPrint([$const]);

        foreach ($const->consts as $c) {
            print $prettyPrinter->prettyPrint([$c]).PHP_EOL;
            print_r($c->name);
        }

        $this->assertTrue(true);
    }

    protected function getConst($statements)
    {
        foreach ($statements as $statement) {
            if (get_class($statement) == \PhpParser\Node\Stmt\ClassConst::class) {
                return $statement;
            }
            if (property_exists($statement, 'stmts')) {
                $return = $this->getConst($statement->stmts);
                if (!empty($return)) {
                    return $return;
                }
            }
        }

        return null;
    }
}
