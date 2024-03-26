<?php

namespace LaravelRocket\Generator\Services;

use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Illuminate\View\Factory as ViewFactory;

class FileService
{
    /** @var \Illuminate\Config\Repository */
    protected $config;

    /** @var \Illuminate\Filesystem\Filesystem */
    protected $files;

    /** @var \Illuminate\View\Factory */
    protected $view;

    /** @var string */
    protected $errorString;

    /** @var bool */
    protected $overwrite;

    /**
     * @param \Illuminate\Config\Repository     $config
     * @param \Illuminate\Filesystem\Filesystem $files
     * @param \Illuminate\View\Factory          $view
     */
    public function __construct(
        ConfigRepository $config,
        Filesystem $files,
        ViewFactory $view = null
    ) {
        $this->config = $config;
        $this->files  = $files;
        $this->view   = $view;
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function alreadyExists(string $path): bool
    {
        return $this->files->exists($path);
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param string $path
     *
     * @return string
     */
    protected function makeDirectory(string $path): string
    {
        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }

        return $path;
    }

    /**
     * @param string $class
     *
     * @return string
     */
    protected function convertClassToPath($class): string
    {
        return base_path(str_replace('\\', '/', str_replace('App', 'app', $class)).'.php');
    }

    /**
     * @param string $view
     * @param string $destinationPath
     * @param array  $variables
     */
    public function render(string $view, string $destinationPath, array $variables = [])
    {
        $addHeader      = Str::endsWith($destinationPath, '.php');
        $isBladeTemplate = Str::endsWith($destinationPath, '.blade.php');

        \View::addLocation(resource_path('stubs'));
        \View::addLocation(implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', 'stubs']));

        $data = $this->view->make($view, $variables)->render();
        if ($addHeader) {
            $data = '<?php'.PHP_EOL.PHP_EOL.$data;
        }
        $data = str_replace('＠', '@', $data);
        if ($isBladeTemplate) {
            $data = str_replace('｛', '{', $data);
            $data = str_replace('｝', '}', $data);
        }
        $this->prepareDirectory($destinationPath);
        $this->files->put($destinationPath, $data);
    }

    protected function prepareDirectory($path)
    {
        $directory = $this->files->dirname($path);
        if (!file_exists($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }
    }

    public function copy(string $sourcePath, string $destinationPath)
    {
        $this->prepareDirectory($destinationPath);
        copy($sourcePath, $destinationPath);
    }

    public function save($destinationPath, $data)
    {
        $this->files->put($destinationPath, $data);
    }
}
