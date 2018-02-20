<?php
namespace LaravelRocket\Generator\Objects;

use PhpParser\Error;
use PhpParser\Lexer;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike as StmtClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\ParserFactory;

class ClassLike
{
    /** @var string $path */
    protected $path;

    /**
     * @var ClassMethod[]
     */
    protected $methods = [];

    /**
     * @var ClassConst[]
     */
    protected $constants = [];

    /**
     * @var Property[]
     */
    protected $properties = [];

    public function __construct($path)
    {
        $this->path = $path;
        $this->parse();
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return  $this->path;
    }

    /**
     * @return \PhpParser\Node\Stmt\ClassMethod[]
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @return \PhpParser\Node\Stmt\ClassConst[]
     */
    public function getConstants()
    {
        return $this->constants;
    }

    /**
     * @return \PhpParser\Node\Stmt\Property[]
     */
    public function getProperties()
    {
        return $this->properties;
    }

    protected function parse()
    {
        $lexer  = new Lexer([
            'usedAttributes' => [
                'comments', 'startLine', 'endLine',
            ],
        ]);
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7, $lexer);

        try {
            $statements = $parser->parse(file_get_contents($this->path));
        } catch (Error $e) {
            return false;
        }

        $name  = pathinfo($this->path, PATHINFO_FILENAME);
        $class = $this->getClassLikeObject($name, $statements);

        if (is_a($class, StmtClassLike::class)) {
            $this->getClassInfo($class);
        }
    }

    /**
     * @param $name
     * @param \PhpParser\Node\Stmt $statements
     *
     * @return null|\PhpParser\Node\Stmt\ClassLike
     */
    protected function getClassLikeObject(string $name, $statements)
    {
        foreach ($statements as $statement) {
            if (is_a($statement, StmtClassLike::class) && $statement->name == $name) {
                return $statement;
            }
            if (property_exists($statement, 'stmts')) {
                $return = $this->getClassLikeObject($name, $statement->stmts);
                if (!empty($return)) {
                    return $return;
                }
            }
        }

        return null;
    }

    /**
     * @param \PhpParser\Node\Stmt\ClassLike $classLike
     */
    protected function getClassInfo($classLike)
    {
        if (!property_exists($classLike, 'stmts')) {
            return;
        }
        foreach ($classLike->stmts as $statement) {
            if (is_a($statement, ClassMethod::class)) {
                $this->methods[$statement->name] = $statement;
            } elseif (is_a($statement, Property::class)) {
                $this->properties[] = $statement;
            } elseif (is_a($statement, ClassConst::class)) {
                $this->constants[] = $statement;
            }
        }
    }
}
