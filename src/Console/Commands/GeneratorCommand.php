<?php

namespace LaravelRocket\Generator\Console\Commands;

use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use LaravelRocket\Generator\Generators\Generator;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Support\Str;
use Illuminate\View\Factory as ViewFactory;

class GeneratorCommand extends Command
{

    protected $name        = '';

    protected $description = '';

    /** @var \Illuminate\Config\Repository */
    protected $config;

    /** @var \Illuminate\Filesystem\Filesystem */
    protected $files;

    /** @var \Illuminate\View\Factory */
    protected $view;

    /** @var string  */
    protected $generator = '';

    /**
     *
     * @param \Illuminate\Config\Repository     $config
     * @param \Illuminate\Filesystem\Filesystem $files
     * @param \Illuminate\View\Factory          $view
     */
    public function __construct(
        ConfigRepository $config,
        Filesystem $files,
        ViewFactory $view
    )
    {
        $this->config = $config;
        $this->files = $files;
        $this->view = $view;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        $targetName = $this->getTargetName();
        $name = $this->parseName($targetName);

        return $this->generate($name);
    }

    public function generate($name)
    {
        /** @var Generator $generator */
        $generator = app()->make($this->generator);

        return $generator->generate($name);
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getTargetName()
    {
        return $this->argument('name');
    }

    /**
     * Parse the name and format according to the root namespace.
     *
     * @param string $name
     *
     * @return string
     */
    protected function parseName($name)
    {
        if (Str::contains($name, '/')) {
            $name = str_replace('/', '\\', $name);
        }

        $names = array_slice(explode('\\', $name), -1, 1);
        return count($names) ? $names[0] : $name;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the class'],
        ];
    }

}
