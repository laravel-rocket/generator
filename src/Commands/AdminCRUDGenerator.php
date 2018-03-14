<?php
namespace LaravelRocket\Generator\Commands;

use LaravelRocket\Generator\FileUpdaters\React\CRUD\Admin\RouterFileUpdater;
use LaravelRocket\Generator\FileUpdaters\React\CRUD\Admin\SideBarFileUpdater;
use LaravelRocket\Generator\Generators\React\CRUD\Admin\ColumnGenerator;
use LaravelRocket\Generator\Generators\React\CRUD\Admin\InfoGenerator;
use LaravelRocket\Generator\Generators\React\CRUD\Admin\RepositoryGenerator;
use LaravelRocket\Generator\Generators\React\CRUD\Admin\ViewGenerator;
use LaravelRocket\Generator\Services\DatabaseService;
use function ICanBoogie\pluralize;
use function ICanBoogie\singularize;

class AdminCRUDGenerator extends MWBGenerator
{
    protected $name = 'rocket:make:crud:admin';

    protected $signature = 'rocket:make:crud:admin {name}  {--rebuild} {--file=} {--json=}';

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
            new RouterFileUpdater($this->config, $this->files, $this->view),
            new SideBarFileUpdater($this->config, $this->files, $this->view),
        ];

        $name  = $this->normalizeName($this->argument('name'));
        $table = $this->findTableFromName($name);
        if (empty($table)) {
            $this->output('No table definition found: '.$name, 'red');

            return;
        }

        $this->output('Processing '.ucfirst(singularize($name)).' Admin CRUD...', 'green');
        foreach ($generators as $generator) {
            $generator->generate($table, $this->tables, $this->json);
        }
        foreach ($fileUpdaters as $fileUpdater) {
            $fileUpdater->insert($table, $this->tables, $this->json);
        }
    }
}
