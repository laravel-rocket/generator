<?php
namespace LaravelRocket\Generator\Commands;

use LaravelRocket\Generator\FileUpdaters\CRUD\Admin\RouterFileUpdater;
use LaravelRocket\Generator\FileUpdaters\CRUD\Admin\SideBarFileUpdater;
use LaravelRocket\Generator\FileUpdaters\Models\RegisterRepositoryFileUpdater;
use LaravelRocket\Generator\Generators\CRUD\Admin\ControllerGenerator as AdminCRUDControllerGenerator;
use LaravelRocket\Generator\Generators\CRUD\Admin\RequestGenerator as AdminCRUDRequestGenerator;
use LaravelRocket\Generator\Generators\CRUD\Admin\TemplateGenerator as AdminCRUDTemplateGenerator;
use LaravelRocket\Generator\Generators\CRUD\Admin\UnitTestGenerator as AdminCRUDUnitTestGenerator;
use LaravelRocket\Generator\Generators\Migrations\MigrationFileGenerator;
use LaravelRocket\Generator\Generators\Models\LanguageFileGenerator;
use LaravelRocket\Generator\Generators\Models\ModelFactoryGenerator;
use LaravelRocket\Generator\Generators\Models\ModelGenerator;
use LaravelRocket\Generator\Generators\Models\ModelUnitTestGenerator;
use LaravelRocket\Generator\Generators\Models\PresenterGenerator;
use LaravelRocket\Generator\Generators\Models\RepositoryGenerator;
use LaravelRocket\Generator\Generators\Models\RepositoryInterfaceGenerator;
use LaravelRocket\Generator\Generators\Models\RepositoryUnitTestGenerator;
use LaravelRocket\Generator\Services\DatabaseService;
use LaravelRocket\Generator\Validators\Error;
use LaravelRocket\Generator\Validators\TableSchemaValidator;

class GenerateFromMWB extends MWBGenerator
{
    protected $name = 'rocket:generate:from-mwb';

    protected $signature = 'rocket:generate:from-mwb {--rebuild} {--file=} {--json=} ';

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

        $this->databaseService = new DatabaseService($this->config, $this->files);
        $databaseName          = $this->databaseService->resetDatabase();

        $this->generateMigration();
        $this->generateModel();

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
        $generateAlterTableMigrationFile = !$this->input->hasOption('rebuild');
        $generator                       = new MigrationFileGenerator($this->config, $this->files, $this->view);
        foreach ($this->tables as $table) {
            $generator->generate($table, $generateAlterTableMigrationFile);
        }

        $this->databaseService->resetDatabase();
    }

    protected function generateModel()
    {
        /** @var \LaravelRocket\Generator\Generators\TableBaseGenerator[] $generators */
        $generators = [
            new ModelGenerator($this->config, $this->files, $this->view),
            new ModelFactoryGenerator($this->config, $this->files, $this->view),
            new ModelUnitTestGenerator($this->config, $this->files, $this->view),
            new PresenterGenerator($this->config, $this->files, $this->view),
            new RepositoryGenerator($this->config, $this->files, $this->view),
            new RepositoryInterfaceGenerator($this->config, $this->files, $this->view),
            new RepositoryUnitTestGenerator($this->config, $this->files, $this->view),
            new LanguageFileGenerator($this->config, $this->files, $this->view),
            new AdminCRUDControllerGenerator($this->config, $this->files, $this->view),
            new AdminCRUDRequestGenerator($this->config, $this->files, $this->view),
            new AdminCRUDUnitTestGenerator($this->config, $this->files, $this->view),
            new AdminCRUDTemplateGenerator($this->config, $this->files, $this->view),
        ];

        /** @var \LaravelRocket\Generator\FileUpdaters\TableBaseFileUpdater[] $fileUpdaters */
        $fileUpdaters = [
            new RegisterRepositoryFileUpdater($this->config, $this->files, $this->view),
            new RouterFileUpdater($this->config, $this->files, $this->view),
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
