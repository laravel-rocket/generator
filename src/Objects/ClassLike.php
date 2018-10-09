<?php
namespace LaravelRocket\Generator\Objects;

use PhpParser\Error;
use PhpParser\Lexer;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike as StmtClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\ParserFactory;

class ClassLike
{
    protected $excludeMethods = [];

    /** @var string $path */
    protected $path;

    /** @var \PhpParser\Node\Stmt[]|\PhpParser\Node[] $statements */
    protected $statements;

    /** @var \PhpParser\Node\Stmt\ClassLike */
    protected $classStatement;

    /**
     * @var string
     */
    protected $nameSpace = '';

    /**
     * @var string
     */
    protected $className = '';

    /**
     * @var ClassMethod[]
     */
    protected $methods = [];

    /**
     * @var \ReflectionClass
     */
    protected $reflection;

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
        return $this->path;
    }

    /**
     * @return \PhpParser\Node\Stmt\ClassMethod[]
     */
    public function getMethods()
    {
        return $this->methods;
    }

    public function fullClassName()
    {
        return '\\'.$this->nameSpace.'\\'.$this->className;
    }

    /**
     * @return null|\ReflectionClass
     */
    public function getReflection()
    {
        if (empty($this->reflection)) {
            try {
                $this->reflection = new \ReflectionClass($this->fullClassName());
            } catch (\ReflectionException $exception) {
                return null;
            }
        }

        return $this->reflection;
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
            $this->statements = $parser->parse(file_get_contents($this->path));
        } catch (Error $e) {
            return false;
        }

        $name                 = pathinfo($this->path, PATHINFO_FILENAME);
        $this->classStatement = $this->getClassLikeObject($name, $this->statements);

        if (is_a($this->classStatement, StmtClassLike::class)) {
            $this->className = $this->classStatement->name;
            $this->getClassInfo($this->classStatement);
        }
    }

    /**
     * @param string                                   $name
     * @param \PhpParser\Node\Stmt[]|\PhpParser\Node[] $statements
     *
     * @return null|StmtClassLike|\PhpParser\Node\Stmt
     */
    protected function getClassLikeObject(string $name, $statements)
    {
        foreach ($statements as $statement) {
            if (is_a($statement, Namespace_::class)) {
                $this->nameSpace = $statement->name;
            }

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
        if (property_exists($classLike, 'implements')) {
        }
        foreach ($classLike->stmts as $statement) {
            if (is_a($statement, ClassMethod::class)) {
                if (!in_array($statement->name->name, $this->excludeMethods) && !starts_with($statement->name, '__')) {
                    $this->methods[$statement->name->name] = $statement;
                }
            } elseif (is_a($statement, Property::class)) {
                $this->properties[] = $statement;
            } elseif (is_a($statement, ClassConst::class)) {
                $this->constants[] = $statement;
            }
        }
    }

    public function getConstructor(array $additionalParams = [])
    {
        $methods       = $this->getMethods();
        $constructor   = null;
        $prettyPrinter = new \PhpParser\PrettyPrinter\Standard;
        foreach ($methods as $method) {
            if ($methods->name === '__construct') {
                $constructor = $method;
            }
        }
        $params    = [];
        $statement = '';
        if ($constructor) {
            foreach ($constructor->params as $param) {
                $name          = $param->name;
                $type          = $param->type->toString();
                $params[$type] = $name;
            }
            $statement = $prettyPrinter->prettyPrint($constructor->getStmts());
        }
        foreach ($additionalParams as $type => $name) {
            if (!array_key_exists($type, $params)) {
            }
        }
    }
}
