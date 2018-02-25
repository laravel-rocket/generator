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
}
