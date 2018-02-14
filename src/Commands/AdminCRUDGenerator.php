<?php
namespace LaravelRocket\Generator\Commands;

use LaravelRocket\Generator\FileUpdaters\CRUD\Admin\RouterFileUpdater;
use LaravelRocket\Generator\FileUpdaters\CRUD\Admin\SideBarFileUpdater;
use LaravelRocket\Generator\Generators\CRUD\Admin\ControllerGenerator as AdminCRUDControllerGenerator;
use LaravelRocket\Generator\Generators\CRUD\Admin\RequestGenerator as AdminCRUDRequestGenerator;
use LaravelRocket\Generator\Generators\CRUD\Admin\TemplateGenerator as AdminCRUDTemplateGenerator;
use LaravelRocket\Generator\Generators\CRUD\Admin\UnitTestGenerator as AdminCRUDUnitTestGenerator;
use LaravelRocket\Generator\Generators\Models\LanguageFileGenerator;
use LaravelRocket\Generator\Services\DatabaseService;
use function ICanBoogie\pluralize;
use function ICanBoogie\singularize;

class AdminCRUDGenerator extends MWBGenerator
{
    protected $name = 'rocket:make:crud:admin';

    protected $signature = 'rocket:make:crud:admin {name} {--file=} {--json=}';

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
        /** @var \LaravelRocket\Generator\Generators\TableBaseGenerator[] $generators */
        $generators = [
            new LanguageFileGenerator($this->config, $this->files, $this->view),
            new AdminCRUDControllerGenerator($this->config, $this->files, $this->view),
            new AdminCRUDRequestGenerator($this->config, $this->files, $this->view),
            new AdminCRUDUnitTestGenerator($this->config, $this->files, $this->view),
            new AdminCRUDTemplateGenerator($this->config, $this->files, $this->view),
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
