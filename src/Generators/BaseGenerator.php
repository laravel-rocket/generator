<?php

namespace LaravelRocket\Generator\Generators;

use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Factory;
use LaravelRocket\Generator\Objects\ClassLike;
use LaravelRocket\Generator\Objects\Definitions;
use LaravelRocket\Generator\Services\FileService;
use PhpParser\Error;
use PhpParser\Lexer;
use PhpParser\ParserFactory;

class BaseGenerator
{
    /** @var \Illuminate\Config\Repository */
    protected $config;

    /** @var \Illuminate\Filesystem\Filesystem */
    protected $files;

    /** @var \Illuminate\View\Factory */
    protected $view;

    /** @var \LaravelRocket\Generator\Services\FileService */
    protected $fileService;

    /** @var bool $rebuild */
    protected $rebuild;

    /** @var \LaravelRocket\Generator\Objects\Definitions $json */
    protected $json;

    /**
     * @param \Illuminate\Config\Repository     $config
     * @param \Illuminate\Filesystem\Filesystem $files
     * @param \Illuminate\View\Factory          $view
     * @param Definitions                       $json
     * @param bool                              $rebuild
     */
    public function __construct(
        Repository $config,
        Filesystem $files,
        Factory $view = null,
        Definitions $json = null,
        bool $rebuild = false
    ) {
        $this->config  = $config;
        $this->files   = $files;
        $this->view    = $view;
        $this->json    = $json;
        $this->rebuild = $rebuild;

        $this->fileService = new FileService($this->config, $this->files, $this->view);
    }

    /**
     * @return bool
     */
    protected function canGenerate(): bool
    {
        return true;
    }

    /**
     * @return string
     */
    protected function getPath(): string
    {
        return '';
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return '';
    }

    public function copyConfigFile($path)
    {
        if (is_array($path)) {
            $path = implode(DIRECTORY_SEPARATOR, $path);
        }

        $sourcePath = implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', 'sources', 'config', $path]);

        $destinationPath = config_path($path);

        if (file_exists($sourcePath)) {
            $this->fileService->copy($sourcePath, $destinationPath);
        }
    }

    public function copyLanguageFile($path)
    {
        if (is_array($path)) {
            $path = implode(DIRECTORY_SEPARATOR, $path);
        }

        $sourcePath      = implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', 'sources', 'resource', 'lang', 'en', $path]);
        $destinationPath = resource_path(implode(DIRECTORY_SEPARATOR, ['lang', 'en', $path]));

        if (file_exists($sourcePath)) {
            $this->fileService->copy($sourcePath, $destinationPath);
        }
    }

    /**
     * @return null|\PhpParser\Node[]
     */
    protected function parseFile()
    {
        $filePath = $this->getPath();

        if (!file_exists($filePath)) {
            return null;
        }

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

    protected function getExistingMethods(bool $removeComments = false): array
    {
        if (!file_exists($this->getPath())) {
            return [];
        }

        $class = new ClassLike($this->getPath());

        $methods       = $class->getMethods();
        $prettyPrinter = new \PhpParser\PrettyPrinter\Standard();
        $result        = [];
        foreach ($methods as $name => $method) {
            $statement = $prettyPrinter->prettyPrint([$method]);
            if ($removeComments) {
                $comments = $method->getAttribute('comments');
                if ($comments && is_array($comments)) {
                    foreach ($comments as $comment) {
                        if ($comment instanceof \PhpParser\Comment\Doc) {
                            continue;
                        }
                        $commentString = $comment->getText();
                        $statement     = preg_replace("/^\s*".preg_quote($commentString, '/')."\s*/", '', $statement);
                    }
                }
            }
            $result[$name] = $statement;
        }

        return $result;
    }

    protected function getUses(): array
    {
        $constants  = [];
        $statements = $this->parseFile();
        if (empty($statements)) {
            return [];
        }

        $this->getAllUses($statements, $constants);

        asort($constants);

        return $constants;
    }

    protected function getConstants(): array
    {
        $constants  = [];
        $statements = $this->parseFile();
        if (empty($statements)) {
            return [];
        }

        $this->getAllConstants($statements, $constants);

        asort($constants);

        return $constants;
    }

    protected function getTraits(): array
    {
        $traits     = [];
        $statements = $this->parseFile();
        if (empty($statements)) {
            return [];
        }

        $this->getAllTraits($statements, $traits);

        asort($traits);

        return $traits;
    }

    protected function getAllUses($statements, &$result)
    {
        $prettyPrinter = new \PhpParser\PrettyPrinter\Standard();
        foreach ($statements as $statement) {
            if (get_class($statement) === \PhpParser\Node\Stmt\Use_::class) {
                foreach ($statement->uses as $use) {
                    $name          = ltrim($prettyPrinter->prettyPrint([$use]));
                    $result[$name] = $name;
                }
            } elseif (property_exists($statement, 'stmts')) {
                $return = $this->getAllUses($statement->stmts, $result);
                if (!empty($return)) {
                    return $return;
                }
            }
        }

        return null;
    }

    protected function getAllTraits($statements, &$result)
    {
        $prettyPrinter = new \PhpParser\PrettyPrinter\Standard();
        foreach ($statements as $statement) {
            if (get_class($statement) === \PhpParser\Node\Stmt\TraitUse::class) {
                /** @var \PhpParser\Node\Name $trait */
                foreach ($statement->traits as $trait) {
                    $result[$trait->toString()] = ltrim($prettyPrinter->prettyPrint([$trait]));
                }
            } elseif (property_exists($statement, 'stmts')) {
                $return = $this->getAllTraits($statement->stmts, $result);
                if (!empty($return)) {
                    return $return;
                }
            }
        }

        return null;
    }

    protected function getAllConstants($statements, &$result)
    {
        $prettyPrinter = new \PhpParser\PrettyPrinter\Standard();
        foreach ($statements as $statement) {
            if (get_class($statement) === \PhpParser\Node\Stmt\ClassConst::class) {
                foreach ($statement->consts as $constant) {
                    $result[$constant->name->name] = ltrim($prettyPrinter->prettyPrint([$constant]));
                }
            } elseif (property_exists($statement, 'stmts')) {
                $return = $this->getAllConstants($statement->stmts, $result);
                if (!empty($return)) {
                    return $return;
                }
            }
        }

        return null;
    }
}
