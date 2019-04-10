<?php
namespace LaravelRocket\Generator\Commands;

use Illuminate\Support\Str;
use LaravelRocket\Generator\Generators\Migrations\MigrationFileGenerator;
use LaravelRocket\Generator\Services\DatabaseService;
use function ICanBoogie\pluralize;

class MigrationGenerator extends MWBGenerator
{
    protected $name = 'rocket:make:migration';

    protected $signature = 'rocket:make:migration {name?} {--rebuild} {--file=} {--json=}';

    protected $description = 'Create Migration';

    /**
     * Execute the console command.
     *
     * @throws \Doctrine\DBAL\DBALException
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

        $this->generateMigration();

        $this->databaseService->dropDatabase();

        return true;
    }

    protected function normalizeName(string $name): string
    {
        return Str::snake(pluralize($name));
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function generateMigration()
    {
        $name = $this->input->getArgument('name');
        if (!empty($name)) {
            $name = $this->normalizeName($name);
        }

        $generateAlterTableMigrationFile = !$this->input->getOption('rebuild');

        $generator                       = new MigrationFileGenerator($this->config, $this->files, $this->view);
        foreach ($this->tables as $table) {
            if (!empty($name) && $name != $table->getName()) {
                continue;
            }
            $result = $generator->generate($table, $generateAlterTableMigrationFile);
            if ($result === false) {
                $this->output($table->getName().' migration create skipped', 'yellow');
            }
        }

        $this->databaseService->resetDatabase();
    }
}
