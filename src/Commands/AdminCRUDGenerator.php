<?php
namespace LaravelRocket\Generator\Commands;

use LaravelRocket\Generator\FileUpdaters\React\CRUD\Admin\RouterFileRouteUpdater;
use LaravelRocket\Generator\FileUpdaters\React\CRUD\Admin\RouterFileUseUpdater;
use LaravelRocket\Generator\FileUpdaters\React\CRUD\Admin\SideBarFileUpdater;
use LaravelRocket\Generator\Generators\React\CRUD\Admin\ColumnGenerator;
use LaravelRocket\Generator\Generators\React\CRUD\Admin\InfoGenerator;
use LaravelRocket\Generator\Generators\React\CRUD\Admin\RepositoryGenerator;
use LaravelRocket\Generator\Generators\React\CRUD\Admin\ViewGenerator;
use LaravelRocket\Generator\Services\DatabaseService;
use function ICanBoogie\pluralize;

class AdminCRUDGenerator extends MWBGenerator
{
    protected $name = 'rocket:make:crud:admin';

    protected $signature = 'rocket:make:crud:admin {name?} {--rebuild} {--file=} {--json=}';

    protected $description = 'Create Admin CRUD';

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
        return snake_case(pluralize($name));
    }

    protected function generate()
    {
        $rebuild = !empty($this->input->getOption('rebuild'));

        /** @var \LaravelRocket\Generator\Generators\TableBaseGenerator[] $generators */
        $generators = [
            new RepositoryGenerator($this->config, $this->files, $this->view, $rebuild),
            new ViewGenerator($this->config, $this->files, $this->view, $rebuild),
            new InfoGenerator($this->config, $this->files, $this->view, $rebuild),
            new ColumnGenerator($this->config, $this->files, $this->view, $rebuild),
        ];

        /** @var \LaravelRocket\Generator\FileUpdaters\TableBaseFileUpdater[] $fileUpdaters */
        $fileUpdaters = [
            new RouterFileRouteUpdater($this->config, $this->files, $this->view),
            new RouterFileUseUpdater($this->config, $this->files, $this->view),
            new SideBarFileUpdater($this->config, $this->files, $this->view),
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

        if (!$rebuild) {
            $this->output('  Warning: if you want to update existing files, please set \'--rebuild\' option', 'yellow');
        }

        foreach ($tables as $table) {
            $this->output('Processing '.$table->getName().' Admin CRUD...', 'green');

            foreach ($generators as $generator) {
                $generator->generate($table, $this->tables, $this->json);
            }
            foreach ($fileUpdaters as $fileUpdater) {
                $fileUpdater->insert($table, $this->tables, $this->json);
            }
        }
    }
}
