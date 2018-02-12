<?php
namespace LaravelRocket\Generator\Commands;

use LaravelRocket\Generator\Generators\Migrations\MigrationFileGenerator;
use LaravelRocket\Generator\Services\DatabaseService;
use function ICanBoogie\pluralize;

class MigrationGenerator extends MWBGenerator
{
    protected $name = 'rocket:make:migration';

    protected $signature = 'rocket:make:repository --name={name} {--use_alter} {--json=}';

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
        $databaseName          = $this->databaseService->resetDatabase();

        $this->generateMigration();

        $this->databaseService->dropDatabase();

        return true;
    }

    protected function normalizeName(string $name): string
    {
        return snake_case(pluralize($name));
    }

    protected function generateMigration()
    {
        $name = $this->input->getOption('name');
        if (!empty($name)) {
            $name = $this->normalizeName($name);
        }

        $generateAlterTableMigrationFile = $this->input->hasOption('use_alter');
        $generator                       = new MigrationFileGenerator($this->config, $this->files, $this->view);
        foreach ($this->tables as $table) {
            if (!empty($name) && $name != $table->getName()) {
                continue;
            }
            $generator->generate($table, $generateAlterTableMigrationFile);
        }

        $this->databaseService->resetDatabase();
    }
}
