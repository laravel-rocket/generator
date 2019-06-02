<?php
namespace LaravelRocket\Generator\Commands;

use function ICanBoogie\pluralize;
use function ICanBoogie\singularize;
use Illuminate\Support\Str;
use LaravelRocket\Generator\Generators\Models\ColumnLanguageFileGenerator;
use LaravelRocket\Generator\Generators\Models\ConfigFileGenerator;
use LaravelRocket\Generator\Generators\Models\ModelFactoryGenerator;
use LaravelRocket\Generator\Generators\Models\ModelUnitTestGenerator;
use LaravelRocket\Generator\Generators\Models\PresenterGenerator;
use LaravelRocket\Generator\Generators\Models\RelationLanguageFileGenerator;
use LaravelRocket\Generator\Services\DatabaseService;

class ModelGenerator extends MWBGenerator
{
    protected $name = 'rocket:make:model';

    protected $signature = 'rocket:make:model {name} {--file=} {--json=}';

    protected $description = 'Create Model';

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
        /** @var \LaravelRocket\Generator\Generators\TableBaseGenerator[] $generators */
        $generators = [
            new \LaravelRocket\Generator\Generators\Models\ModelGenerator($this->config, $this->files, $this->view, $this->json),
            new ModelFactoryGenerator($this->config, $this->files, $this->view, $this->json),
            new ModelUnitTestGenerator($this->config, $this->files, $this->view, $this->json),
            new PresenterGenerator($this->config, $this->files, $this->view, $this->json),
            new ColumnLanguageFileGenerator($this->config, $this->files, $this->view, $this->json),
            new RelationLanguageFileGenerator($this->config, $this->files, $this->view, $this->json),
            new ConfigFileGenerator($this->config, $this->files, $this->view, $this->json),
        ];

        /** @var \LaravelRocket\Generator\FileUpdaters\TableBaseFileUpdater[] $fileUpdaters */
        $fileUpdaters = [
        ];

        $name = $this->normalizeName($this->argument('name'));

        $table = $this->findTableFromName($name);
        if (empty($table)) {
            $this->output('No table definition found: '.$name, 'red');

            return;
        }

        $this->output('Processing '.ucfirst(singularize($name)).' ...', 'green');
        foreach ($generators as $generator) {
            $generator->generate($table, $this->tables, $this->json);
        }
        foreach ($fileUpdaters as $fileUpdater) {
            $fileUpdater->insert($table, $this->tables, $this->json);
        }
    }
}
