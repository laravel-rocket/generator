<?php

namespace LaravelRocket\Generator\FileUpdaters;

use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Factory;
use LaravelRocket\Generator\Services\FileService;
use PhpParser\Error;
use PhpParser\Lexer;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\ParserFactory;

class BaseFileUpdater
{
    /** @var \Illuminate\Config\Repository */
    protected $config;

    /** @var \Illuminate\Filesystem\Filesystem */
    protected $files;

    /** @var \Illuminate\View\Factory */
    protected $view;

    /** @var \LaravelRocket\Generator\Services\FileService */
    protected $fileService;

    /** @var bool */
    protected $rebuild;

    /**
     * @param \Illuminate\Config\Repository     $config
     * @param \Illuminate\Filesystem\Filesystem $files
     * @param \Illuminate\View\Factory          $view
     * @param bool                              $rebuild
     */
    public function __construct(
        Repository $config,
        Filesystem $files,
        Factory $view = null,
        bool $rebuild = false
    ) {
        $this->config = $config;
        $this->files  = $files;
        $this->view   = $view;

        $this->fileService = new FileService($this->config, $this->files, $this->view);
    }

    protected function getTargetFilePath(): string
    {
        return '';
    }

    /**
     * @return int
     */
    protected function getInsertPosition(): int
    {
        return -1;
    }

    /**
     * @return int
     */
    protected function getExistingPosition(): int
    {
        return -1;
    }

    /**
     * @return string
     */
    protected function getInsertData(): string
    {
        return '';
    }

    /**
     * @param string $filePath
     * @param string $methodName
     *
     * @return int
     */
    protected function getEndOfMethodPosition(string $filePath, string $methodName): int
    {
        /** @var \PhpParser\Node\Stmt\ClassMethod|null $method */
        $method = $this->getMethodObject($filePath, $methodName);
        if (empty($method)) {
            return -1;
        }

        return $method->getAttribute('endLine', -1);
    }

    protected function getEndOfArrayItemArray(string $filePath, string $keyName): int
    {
        $item = $this->getArrayKey($filePath, $keyName);
        if (empty($item)) {
            return -1;
        }

        return $item->getAttribute('endLine', -1);
    }

    protected function getEndOfProperty(string $filePath, string $keyName): int
    {
        /** @var \PhpParser\Node\Stmt\PropertyProperty|null $item */
        $item = $this->getProperty($filePath, $keyName);
        if (empty($item)) {
            return -1;
        }

        return $item->getAttribute('endLine', -1);
    }

    /**
     * @param string $data
     * @param string $filePath
     * @param int    $lineNumber
     *
     * @return bool
     */
    protected function insertDataToLine(string $data, string $filePath, int $lineNumber): bool
    {
        $lines = file($filePath);
        if ($lines === false) {
            return false;
        }

        if (count($lines) + 1 < $lineNumber) {
            $lineNumber = count($lines) + 1;
        }
        if ($lineNumber < 1) {
            $lineNumber = 1;
        }

        array_splice($lines, $lineNumber - 1, 0, [$data]);

        $result = file_put_contents($filePath, implode('', $lines));
        if ($result === false) {
            return false;
        }

        return true;
    }

    /**
     * @param string $filePath
     *
     * @return null|\PhpParser\Node[]
     */
    protected function parseFile(string $filePath)
    {
        $lexer = new Lexer([
            'usedAttributes' => [
                'comments', 'startLine', 'endLine',
            ],
        ]);

        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7, $lexer);

        try {
            $statements = $parser->parse(file_get_contents($filePath));
        } catch (Error $e) {
            return null;
        }

        return $statements;
    }

    /**
     * @param string $filePath
     * @param string $methodName
     *
     * @return null
     */
    protected function getMethodObject(string $filePath, string $methodName)
    {
        $statements = $this->parseFile($filePath);
        if (empty($statements)) {
            return null;
        }
        $method = $this->getFunction($methodName, $statements);
        if (empty($method)) {
            return null;
        }

        return $method;
    }

    /**
     * @param string            $name
     * @param \PhpParser\Node[] $statements
     *
     * @return null
     */
    protected function getFunction(string $name, $statements)
    {
        foreach ($statements as $statement) {
            if (get_class($statement) == ClassMethod::class && $statement->name == $name) {
                return $statement;
            }
            if (property_exists($statement, 'stmts')) {
                $result = $this->getFunction($name, $statement->stmts);
                if (!empty($result)) {
                    return $result;
                }
            }
        }

        return null;
    }

    /**
     * @param \PhpParser\Node[] $statements
     *
     * @return array
     */
    protected function getAllMethods($statements)
    {
        $methods = [];
        foreach ($statements as $statement) {
            if (get_class($statement) == ClassMethod::class) {
                $methods[$statement->name] = $statement;
            }
            if (property_exists($statement, 'stmts')) {
                $result = $this->getAllMethods($statement->stmts);
                if (!empty($result)) {
                    $methods = array_merge($methods, $result);
                }
            }
        }

        return $methods;
    }

    /**
     * @param string $filePath
     * @param string $keyName
     *
     * @return \PhpParser\Node\Expr|null
     */
    protected function getArrayKey(string $filePath, string $keyName)
    {
        $statements = $this->parseFile($filePath);
        if (empty($statements)) {
            return null;
        }

        return $this->getArrayItem($keyName, $statements);
    }

    /**
     * @param string            $name
     * @param \PhpParser\Node[] $statements
     *
     * @return \PhpParser\Node\Expr|null
     */
    protected function getArrayItem(string $name, $statements)
    {
        foreach ($statements as $statement) {
            if (get_class($statement) == ArrayItem::class && $statement->key && $statement->key->value === $name) {
                return $statement->value;
            } elseif (property_exists($statement, 'stmts')) {
                $result = $this->getArrayItem($name, $statement->stmts);
            } elseif (property_exists($statement, 'expr') &&
                property_exists($statement->expr, 'items')
            ) {
                $result = $this->getArrayItem($name, $statement->expr->items);
            }
            if (!empty($result)) {
                return $result;
            }
        }

        return null;
    }

    /**
     * @param string $filePath
     * @param string $keyName
     *
     * @return \PhpParser\Node\Stmt\PropertyProperty|null
     */
    protected function getProperty(string $filePath, string $keyName)
    {
        $statements = $this->parseFile($filePath);
        if (empty($statements)) {
            return null;
        }

        return $this->getPropertyRecursive($keyName, $statements);
    }

    /**
     * @param string            $name
     * @param \PhpParser\Node[] $statements
     *
     * @return \PhpParser\Node\Stmt\PropertyProperty|null
     */
    protected function getPropertyRecursive(string $name, $statements)
    {
        foreach ($statements as $statement) {
            if ($statement instanceof \PhpParser\Node\Stmt\PropertyProperty && $statement->name == $name) {
                return $statement;
            } elseif (property_exists($statement, 'stmts')) {
                $result = $this->getPropertyRecursive($name, $statement->stmts);
            } elseif (property_exists($statement, 'expr')
                && property_exists($statement->expr, 'items')
            ) {
                $result = $this->getPropertyRecursive($name, $statement->expr->items);
            } elseif (property_exists($statement, 'default')
                && property_exists($statement->default, 'items')
            ) {
                $result = $this->getPropertyRecursive($name, $statement->default->items);
            } elseif (property_exists($statement, 'props')) {
                $result = $this->getPropertyRecursive($name, $statement->props);
            }
            if (!empty($result)) {
                return $result;
            }
        }

        return null;
    }
}
