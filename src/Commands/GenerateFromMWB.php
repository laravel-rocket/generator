<?php
namespace LaravelRocket\Generator\Commands;

use LaravelRocket\Generator\FileUpdaters\APIs\Admin\RouterFileUpdater;
use LaravelRocket\Generator\FileUpdaters\Models\RegisterRepositoryFileUpdater;
use LaravelRocket\Generator\FileUpdaters\React\CRUD\Admin\RouterFileRouteUpdater;
use LaravelRocket\Generator\FileUpdaters\React\CRUD\Admin\RouterFileUseUpdater;
use LaravelRocket\Generator\FileUpdaters\React\CRUD\Admin\SideBarFileUpdater;
use LaravelRocket\Generator\Generators\APIs\Admin\ControllerGenerator;
use LaravelRocket\Generator\Generators\APIs\Admin\ListResponseGenerator;
use LaravelRocket\Generator\Generators\APIs\Admin\RequestGenerator;
use LaravelRocket\Generator\Generators\APIs\Admin\ResponseGenerator;
use LaravelRocket\Generator\Generators\APIs\Admin\UnitTestGenerator;
use LaravelRocket\Generator\Generators\Migrations\MigrationFileGenerator;
use LaravelRocket\Generator\Generators\Models\ColumnLanguageFileGenerator;
use LaravelRocket\Generator\Generators\Models\ConfigFileGenerator;
use LaravelRocket\Generator\Generators\Models\ModelFactoryGenerator;
use LaravelRocket\Generator\Generators\Models\ModelGenerator;
use LaravelRocket\Generator\Generators\Models\ModelUnitTestGenerator;
use LaravelRocket\Generator\Generators\Models\PresenterGenerator;
use LaravelRocket\Generator\Generators\Models\RelationLanguageFileGenerator;
use LaravelRocket\Generator\Generators\Models\RepositoryGenerator;
use LaravelRocket\Generator\Generators\Models\RepositoryInterfaceGenerator;
use LaravelRocket\Generator\Generators\Models\RepositoryUnitTestGenerator;
use LaravelRocket\Generator\Generators\React\CRUD\Admin\ColumnGenerator;
use LaravelRocket\Generator\Generators\React\CRUD\Admin\InfoGenerator;
use LaravelRocket\Generator\Generators\React\CRUD\Admin\ViewGenerator;
use LaravelRocket\Generator\Services\DatabaseService;
use LaravelRocket\Generator\Validators\Error;
use LaravelRocket\Generator\Validators\Tables\TableSchemaValidator;

class GenerateFromMWB extends MWBGenerator
{
    protected $name = 'rocket:generate:from-mwb';

    protected $signature = 'rocket:generate:from-mwb {--rebuild} {--file=} {--table=} {--json=} ';

    protected $description = 'Create Migrations/Models/Repositories';

    /**
     * Execute the console command.
     *
     * @return bool|null
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function handle()
    {
        $this->tables = $this->getTablesFromMWBFile();
        if ($this->tables === false) {
            return false;
        }
        $this->getAppJson();

        $success = $this->validateTableSchema();
        if (!$success) {
            return false;
        }

        $tableName = $this->option('table');
        if (!empty($tableName)) {
            $table = $this->findTableFromName($tableName);
            if (empty($table)) {
                $this->output('Table ( '.$tableName.' ) doesn\'t exist.', 'error');

                return false;
            }
            $this->tables = [$table];
        }

        $this->databaseService = new DatabaseService($this->config, $this->files);
        $this->databaseService->resetDatabase();

        $this->generateMigration();
        $this->generateModel();

        $this->styleCode();

        $this->databaseService->dropDatabase();

        return true;
    }

    protected function validateTableSchema()
    {
        $validator = new TableSchemaValidator($this->config, $this->files, $this->view);

        /** @var bool $success */
        /** @var \LaravelRocket\Generator\Validators\Error[] $errors */
        list($success, $errors) = $validator->validate($this->tables, $this->json);

        $this->output('Table Schema Validation Result');
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $line = $error->getMessage().' : '.$error->getTarget();
                switch ($error->getLevel()) {
                    case Error::LEVEL_ERROR:
                        $this->output($line, 'red');
                        break;
                    case Error::LEVEL_WARNING:
                        $this->output($line, 'yellow');
                        break;
                    case Error::LEVEL_INFO:
                    default:
                        $this->output('  '.$line);
                        break;
                }
                $suggestions = $error->getSuggestions();
                if (count($suggestions) > 0) {
                    foreach ($suggestions as $suggestion) {
                        $this->output('    '.$suggestion);
                    }
                }
            }
        } else {
            $this->output('  > No Problem found.', 'green');
        }

        return $success;
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function generateMigration()
    {
        $generateAlterTableMigrationFile = empty($this->input->getOption('rebuild'));
        $generator                       = new MigrationFileGenerator($this->config, $this->files, $this->view);
        foreach ($this->tables as $table) {
            $generator->generate($table, $generateAlterTableMigrationFile);
        }

        $this->databaseService->resetDatabase();
    }

    protected function generateModel()
    {
        $rebuild = !empty($this->input->getOption('rebuild'));

        /** @var \LaravelRocket\Generator\Generators\TableBaseGenerator[] $generators */
        $generators = [
            new ModelGenerator($this->config, $this->files, $this->view, $rebuild),
            new ModelFactoryGenerator($this->config, $this->files, $this->view, $rebuild),
            new ModelUnitTestGenerator($this->config, $this->files, $this->view, $rebuild),
            new PresenterGenerator($this->config, $this->files, $this->view, $rebuild),
            new RepositoryGenerator($this->config, $this->files, $this->view, $rebuild),
            new RepositoryInterfaceGenerator($this->config, $this->files, $this->view, $rebuild),
            new RepositoryUnitTestGenerator($this->config, $this->files, $this->view, $rebuild),
            new ColumnLanguageFileGenerator($this->config, $this->files, $this->view, $rebuild),
            new RelationLanguageFileGenerator($this->config, $this->files, $this->view),
            new ConfigFileGenerator($this->config, $this->files, $this->view, $rebuild),

            //Admin API
            new ResponseGenerator($this->config, $this->files, $this->view, $rebuild),
            new ListResponseGenerator($this->config, $this->files, $this->view, $rebuild),
            new ControllerGenerator($this->config, $this->files, $this->view, $rebuild),
            new UnitTestGenerator($this->config, $this->files, $this->view, $rebuild),
            new RequestGenerator($this->config, $this->files, $this->view, $rebuild),

            // Admin CRUD
            new \LaravelRocket\Generator\Generators\React\CRUD\Admin\RepositoryGenerator($this->config, $this->files, $this->view, $rebuild),
            new ViewGenerator($this->config, $this->files, $this->view, $rebuild),
            new InfoGenerator($this->config, $this->files, $this->view, $rebuild),
            new ColumnGenerator($this->config, $this->files, $this->view, $rebuild),
        ];

        /** @var \LaravelRocket\Generator\FileUpdaters\TableBaseFileUpdater[] $fileUpdaters */
        $fileUpdaters = [
            new RegisterRepositoryFileUpdater($this->config, $this->files, $this->view, $rebuild),

            //Admin API
            new RouterFileUpdater($this->config, $this->files, $this->view),

            // Admin CRUD
            new RouterFileRouteUpdater($this->config, $this->files, $this->view),
            new RouterFileUseUpdater($this->config, $this->files, $this->view),
            new SideBarFileUpdater($this->config, $this->files, $this->view),
        ];

        foreach ($this->tables as $table) {
            $this->output('Processing '.$table->getName().'...', 'green');
            foreach ($generators as $generator) {
                $generator->generate($table, $this->tables, $this->json);
            }
            foreach ($fileUpdaters as $fileUpdater) {
                $fileUpdater->insert($table, $this->tables, $this->json);
            }
        }
    }
}
