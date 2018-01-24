<?php
namespace LaravelRocket\Generator\Generators;

use LaravelRocket\Generator\Services\FileService;

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

    /**
     * @param \Illuminate\Config\Repository     $config
     * @param \Illuminate\Filesystem\Filesystem $files
     * @param \Illuminate\View\Factory          $view
     */
    public function __construct(
        \Illuminate\Config\Repository $config,
        \Illuminate\Filesystem\Filesystem $files,
        \Illuminate\View\Factory $view = null
    ) {
        $this->config = $config;
        $this->files  = $files;
        $this->view   = $view;

        $this->fileService = new FileService($this->config, $this->files, $this->view);
    }

    public function copyConfigFile($path)
    {
        if (is_array($path)) {
            $path = implode(DIRECTORY_SEPARATOR, $path);
        }

        $sourcePath = implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', 'source', 'config', $path]);

        $destinationPath = config_path($path);

        if (file_exists($sourcePath)) {
            copy($sourcePath, $destinationPath);
        }
    }

    public function copyLanguageFile($path)
    {
        if (is_array($path)) {
            $path = implode(DIRECTORY_SEPARATOR, $path);
        }

        $sourcePath      = implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', 'source', 'resource', 'lang', 'en', $path]);
        $destinationPath = resource_path(implode(DIRECTORY_SEPARATOR, ['lang', 'en', $path]));

        if (file_exists($sourcePath)) {
            copy($sourcePath, $destinationPath);
        }
    }
}
