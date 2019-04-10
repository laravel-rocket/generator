<?php
namespace LaravelRocket\Generator\Validators;

use Illuminate\Support\Str;
use LaravelRocket\Generator\Services\FileService;

class BaseValidator
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

    /**
     * @param string $path
     * @param string $postfix
     *
     * @return string[]
     */
    public function getDirectoryFiles(string $path, string $postfix = '')
    {
        if (!is_dir($path)) {
            return [];
        }

        $results = [];
        $files   = array_diff(scandir($path), ['..', '.']);
        foreach ($files as $file) {
            $absolutePath = $path.DIRECTORY_SEPARATOR.$file;
            if (!is_dir($absolutePath) && empty($postfix) || Str::endsWith($file, $postfix)) {
                $results[] = $absolutePath;
            }
        }

        return $results;
    }
}
