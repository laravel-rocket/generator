<?php

namespace LaravelRocket\Generator\Commands;

use Illuminate\Support\Str;
use LaravelRocket\Generator\FileUpdaters\Models\RegisterRepositoryFileUpdater;
use LaravelRocket\Generator\Generators\Models\RepositoryInterfaceGenerator;
use LaravelRocket\Generator\Generators\Models\RepositoryUnitTestGenerator;
use LaravelRocket\Generator\Services\DatabaseService;

use function ICanBoogie\pluralize;

class RepositoryGenerator extends MWBGenerator
{
    protected $name = 'rocket:make:repository';

    protected $signature = 'rocket:make:repository {name} {--file=} {--json=}';

    protected $description = 'Create Repository';

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        $this->tables = $this->getTablesFromMWBFile();
        if ($this->tables === false) {
            return false;
        }
        $this->getAppJson();

        $this->databaseService = new DatabaseService($this->config, $this->files);
        $this->databaseService->resetDatabase();

        $this->generate();

        $this->databaseService->dropDatabase();

        return true;
    }

    protected function normalizeName(string $name): string
    {
        if (Str::endsWith(strtolower($name), 'repository')) {
            $name = substr($name, 0, strlen($name) - 10);
        }

        return Str::snake(pluralize($name));
    }

    protected function generate()
    {
        /** @var \LaravelRocket\Generator\Generators\TableBaseGenerator[] $generators */
        $generators = [
            new \LaravelRocket\Generator\Generators\Models\RepositoryGenerator($this->config, $this->files, $this->view, $this->json),
            new RepositoryInterfaceGenerator($this->config, $this->files, $this->view, $this->json),
            new RepositoryUnitTestGenerator($this->config, $this->files, $this->view, $this->json),
        ];

        /** @var \LaravelRocket\Generator\FileUpdaters\TableBaseFileUpdater[] $fileUpdaters */
        $fileUpdaters = [
            new RegisterRepositoryFileUpdater($this->config, $this->files, $this->view),
        ];

        $name = $this->normalizeName($this->argument('name'));

        $table = $this->findTableFromName($name);
        if (empty($table)) {
            $this->output('No table definition found: '.$name, 'red');

            return;
        }

        $this->output('Processing '.$name.'Repository...', 'green');
        foreach ($generators as $generator) {
            $generator->generate($table, $this->tables, $this->json);
        }
        foreach ($fileUpdaters as $fileUpdater) {
            $fileUpdater->insert($table, $this->tables, $this->json);
        }
    }
}
