<?php

namespace LaravelRocket\Generator\Commands;

use Illuminate\Support\Str;
use LaravelRocket\Generator\FileUpdaters\APIs\Admin\RouterFileUpdater;
use LaravelRocket\Generator\Generators\APIs\Admin\ControllerGenerator;
use LaravelRocket\Generator\Generators\APIs\Admin\ListResponseGenerator;
use LaravelRocket\Generator\Generators\APIs\Admin\RequestGenerator;
use LaravelRocket\Generator\Generators\APIs\Admin\ResponseGenerator;
use LaravelRocket\Generator\Generators\APIs\Admin\UnitTestGenerator;
use LaravelRocket\Generator\Services\DatabaseService;

use function ICanBoogie\pluralize;
use function ICanBoogie\singularize;

class AdminAPIGenerator extends MWBGenerator
{
    protected $name = 'rocket:make:api:admin';

    protected $signature = 'rocket:make:api:admin {name?} {--file=} {--json=} {--rebuild}';

    protected $description = 'Create Admin API for CRUD';

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
        return Str::snake(pluralize($name));
    }

    protected function generate()
    {
        $rebuild = !empty($this->input->getOption('rebuild'));

        /** @var \LaravelRocket\Generator\Generators\TableBaseGenerator[] $generators */
        $generators = [
            new ResponseGenerator($this->config, $this->files, $this->view, $this->json, $rebuild),
            new ListResponseGenerator($this->config, $this->files, $this->view, $this->json, $rebuild),
            new ControllerGenerator($this->config, $this->files, $this->view, $this->json, $rebuild),
            new UnitTestGenerator($this->config, $this->files, $this->view, $this->json, $rebuild),
            new RequestGenerator($this->config, $this->files, $this->view, $this->json, $rebuild),
        ];

        /** @var \LaravelRocket\Generator\FileUpdaters\TableBaseFileUpdater[] $fileUpdaters */
        $fileUpdaters = [
            new RouterFileUpdater($this->config, $this->files, $this->view, $rebuild),
        ];

        $name = $this->argument('name');
        if (!empty($name)) {
            $name = $this->normalizeName($name);

            $table = $this->findTableFromName($name);
            if (empty($table)) {
                $this->output('No table definition found: '.$name, 'red');

                return;
            }
            $tables = [$table];
        } else {
            $tables = $this->tables;
        }

        foreach ($tables as $table) {
            $this->output('Processing '.ucfirst(singularize($table->getName())).' Admin API...', 'green');
            foreach ($generators as $generator) {
                $generator->generate($table, $this->tables, $this->json);
            }
            foreach ($fileUpdaters as $fileUpdater) {
                $fileUpdater->insert($table, $this->tables, $this->json);
            }
        }
    }
}
