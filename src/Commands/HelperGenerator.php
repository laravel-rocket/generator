<?php
namespace LaravelRocket\Generator\Commands;

use LaravelRocket\Generator\FileUpdaters\Helpers\AppConfigFileUpdater;
use LaravelRocket\Generator\FileUpdaters\Helpers\RegisterHelperFileUpdater;
use LaravelRocket\Generator\Generators\Helpers\FacadeGenerator;
use LaravelRocket\Generator\Generators\Helpers\HelperInterfaceGenerator;
use LaravelRocket\Generator\Generators\Helpers\HelperUnitTestGenerator;
use function ICanBoogie\singularize;

class HelperGenerator extends BaseCommand
{
    protected $name = 'rocket:make:helper';

    protected $signature = 'rocket:make:helper {name} {--json=}';

    protected $description = 'Create Helper';

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        $this->getAppJson();
        $this->generateHelper();

        return true;
    }

    protected function normalizeName(string $name): string
    {
        if (ends_with(strtolower($name), 'helper')) {
            $name = substr($name, 0, strlen($name) - 6);
        }

        return ucfirst(camel_case(singularize($name)));
    }

    protected function generateHelper()
    {
        /** @var \LaravelRocket\Generator\Generators\NameBaseGenerator[] $generators */
        $generators = [
            new \LaravelRocket\Generator\Generators\Helpers\HelperGenerator($this->config, $this->files, $this->view),
            new HelperInterfaceGenerator($this->config, $this->files, $this->view),
            new HelperUnitTestGenerator($this->config, $this->files, $this->view),
            new FacadeGenerator($this->config, $this->files, $this->view),
        ];

        /** @var \LaravelRocket\Generator\FileUpdaters\NameBaseFileUpdater[] $fileUpdaters */
        $fileUpdaters = [
            new RegisterHelperFileUpdater($this->config, $this->files, $this->view),
            new AppConfigFileUpdater($this->config, $this->files, $this->view),
        ];

        $name = $this->normalizeName($this->argument('name'));

        $this->output('Processing '.$name.'Helper...', 'green');
        foreach ($generators as $generator) {
            $generator->generate($name, $this->json);
        }
        foreach ($fileUpdaters as $fileUpdater) {
            $fileUpdater->insert($name);
        }
    }
}
