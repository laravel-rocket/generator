<?php

namespace LaravelRocket\Generator\Commands;

use Illuminate\Support\Str;
use LaravelRocket\Generator\FileUpdaters\Services\RegisterServiceFileUpdater;
use LaravelRocket\Generator\Generators\Services\ServiceInterfaceGenerator;
use LaravelRocket\Generator\Generators\Services\ServiceUnitTestGenerator;

use function ICanBoogie\singularize;

class ServiceTestGenerator extends BaseCommand
{
    protected $name = 'rocket:make:test:service';

    protected $signature = 'rocket:make:test:service {name}';

    protected $description = 'Add unit test for each service method';

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        $this->generateService();

        return true;
    }

    protected function normalizeName(string $name): string
    {
        if (Str::endsWith(strtolower($name), 'service')) {
            $name = substr($name, 0, strlen($name) - 7);
        }

        return ucfirst(Str::camel(singularize($name)));
    }

    protected function generateService()
    {
        $rebuild = !empty($this->input->getOption('rebuild'));

        /** @var \LaravelRocket\Generator\Generators\NameBaseGenerator[] $generators */
        $generators = [
            new \LaravelRocket\Generator\Generators\Services\ServiceGenerator($this->config, $this->files, $this->view, $this->json, $rebuild),
            new ServiceInterfaceGenerator($this->config, $this->files, $this->view, $this->json, $rebuild),
            new ServiceUnitTestGenerator($this->config, $this->files, $this->view, $this->json, $rebuild),
        ];

        /** @var \LaravelRocket\Generator\FileUpdaters\NameBaseFileUpdater[] $fileUpdaters */
        $fileUpdaters = [
            new RegisterServiceFileUpdater($this->config, $this->files, $this->view, $rebuild),
        ];

        $name = $this->normalizeName($this->argument('name'));

        $this->output('Processing '.$name.'Service...', 'green');
        foreach ($generators as $generator) {
            $generator->generate($name, $this->json);
        }
        foreach ($fileUpdaters as $fileUpdater) {
            $fileUpdater->insert($name);
        }
    }
}
