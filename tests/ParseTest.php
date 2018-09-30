<?php

namespace LaravelRocket\Generator\Tests;

use PhpParser\Lexer;
use PhpParser\Node\Expr\ArrayItem;
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

        $statements = $parser->parse(file_get_contents(__DIR__ . '/data/test.php'));
        $this->travarse($statements);
        $const = $this->getConst($statements);

        //       $prettyPrinter = new \PhpParser\PrettyPrinter\Standard;
        //       print $prettyPrinter->prettyPrint([$const]);

        //        foreach($const->consts as $c) {
        //            print $prettyPrinter->prettyPrint([$c]).PHP_EOL;
        //            print_r($c->name);
        //        }

        $this->assertTrue(true);
    }

    /**
     * @param $statements
     *
     * @return null
     */
    protected function getConst($statements)
    {
        foreach($statements as $statement) {
            if(get_class($statement) == \PhpParser\Node\Stmt\ClassConst::class) {
                return $statement;
            }
            if(property_exists($statement, 'stmts')) {
                $return = $this->getConst($statement->stmts);
                if(!empty($return)) {
                    return $return;
                }
            }
        }

        return null;
    }

    protected function travarse($statements)
    {
        $prettyPrinter = new \PhpParser\PrettyPrinter\Standard;
        foreach($statements as $statement) {
            print get_class($statement) . PHP_EOL;
            if(get_class($statement) === 'PhpParser\Node\Stmt\ClassMethod') {
                print_r($statement->getAttribute('comments'));
            }
            if(get_class($statement) == \PhpParser\Node\Stmt\Use_::class) {
                foreach($statement->uses as $use) {
                    print $use->name . PHP_EOL;
                    print ltrim($prettyPrinter->prettyPrint([$use])) . PHP_EOL;
                }
            } elseif(property_exists($statement, 'stmts')) {
                $this->travarse($statement->stmts);
            } elseif(property_exists($statement, 'expr')) {
                if(property_exists($statement->expr, 'items'))
                    $this->travarse($statement->expr->items);
            }
        }
    }
}
