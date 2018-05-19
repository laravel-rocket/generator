<?php
namespace LaravelRocket\Generator\Generators;

use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Factory;
use LaravelRocket\Generator\Objects\ClassLike;
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

    /** @var bool */
    protected $rebuild;

    protected $parsedFile;

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
        $this->config  = $config;
        $this->files   = $files;
        $this->view    = $view;
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
     * @param string $filePath
     *
     * @return null|\PhpParser\Node[]
     */
    protected function parseFile(string $filePath = '')
    {
        if (!empty($this->parsedFile)) {
            return $this->parsedFile;
        }

        if (empty($filePath)) {
            $filePath = $this->getPath();
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

        $this->parsedFile = $statements;

        return $this->parsedFile;
    }

    protected function getExistingMethods(): array
    {
        if (!file_exists($this->getPath())) {
            return [];
        }

        $class = new ClassLike($this->getPath());

        $methods       = $class->getMethods();
        $prettyPrinter = new \PhpParser\PrettyPrinter\Standard;
        $result        = [];
        foreach ($methods as $name => $method) {
            $result[$name] = $prettyPrinter->prettyPrint([$method]);
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

        $columns = $this->json->get(['tables', $this->table->getName().'.columns'], []);
        foreach ($columns as $name => $column) {
            $type = array_get($column, 'type');
            if ($type === 'type') {
                $options = array_get($column, 'options', []);
                foreach ($options as $option) {
                    $value                    = array_get($option, 'value');
                    $constantName             = $this->generateConstantName($name, $value);
                    $constants[$constantName] = "$constantName = '$value'";
                }
            }
        }

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
        $prettyPrinter = new \PhpParser\PrettyPrinter\Standard;
        foreach ($statements as $statement) {
            if (get_class($statement) === \PhpParser\Node\Stmt\Use_::class) {
                foreach ($statement->uses as $use) {
                    $result[$use->name] = ltrim($prettyPrinter->prettyPrint([$use]));
                }
            } elseif (property_exists($statement, 'stmts')) {
                $return = $this->getAllTraits($statement->stmts, $result);
                if (!empty($return)) {
                    return $return;
                }
            }
        }
    }

    protected function getAllTraits($statements, &$result)
    {
        $prettyPrinter = new \PhpParser\PrettyPrinter\Standard;
        foreach ($statements as $statement) {
            if (get_class($statement) === \PhpParser\Node\Stmt\TraitUse::class) {
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
    }

    protected function getAllConstants($statements, &$result)
    {
        $prettyPrinter = new \PhpParser\PrettyPrinter\Standard;
        foreach ($statements as $statement) {
            if (get_class($statement) === \PhpParser\Node\Stmt\ClassConst::class) {
                foreach ($statement->consts as $constant) {
                    $result[$constant->name] = ltrim($prettyPrinter->prettyPrint([$constant]));
                }
            } elseif (property_exists($statement, 'stmts')) {
                $return = $this->getAllConstants($statement->stmts, $result);
                if (!empty($return)) {
                    return $return;
                }
            }
        }
    }
}
