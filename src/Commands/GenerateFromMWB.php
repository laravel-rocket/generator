<?php
namespace LaravelRocket\Generator\Commands;

use LaravelRocket\Generator\FileUpdaters\CRUD\Admin\RouterFileUpdater;
use LaravelRocket\Generator\FileUpdaters\CRUD\Admin\SideBarFileUpdater;
use LaravelRocket\Generator\FileUpdaters\Models\RegisterRepositoryFileUpdater;
use LaravelRocket\Generator\Generators\CRUD\Admin\ControllerGenerator as AdminCRUDControllerGenerator;
use LaravelRocket\Generator\Generators\CRUD\Admin\RequestGenerator as AdminCRUDRequestGenerator;
use LaravelRocket\Generator\Generators\CRUD\Admin\TemplateGenerator as AdminCRUDTemplateGenerator;
use LaravelRocket\Generator\Generators\CRUD\Admin\UnitTestGenerator as AdminCRUDUnitTestGenerator;
use LaravelRocket\Generator\Generators\MigrationFileGenerator;
use LaravelRocket\Generator\Generators\Models\LanguageFileGenerator;
use LaravelRocket\Generator\Generators\Models\ModelFactoryGenerator;
use LaravelRocket\Generator\Generators\Models\ModelGenerator;
use LaravelRocket\Generator\Generators\Models\ModelUnitTestGenerator;
use LaravelRocket\Generator\Generators\Models\PresenterGenerator;
use LaravelRocket\Generator\Generators\Models\RepositoryGenerator;
use LaravelRocket\Generator\Generators\Models\RepositoryInterfaceGenerator;
use LaravelRocket\Generator\Services\DatabaseService;
use LaravelRocket\Generator\Validators\Error;
use LaravelRocket\Generator\Validators\TableSchemaValidator;
use TakaakiMizuno\MWBParser\Parser;

class GenerateFromMWB extends BaseCommand
{
    protected $name = 'rocket:generate:from-mwb';

    protected $signature = 'rocket:generate:from-mwb {--file=} {--json=}';

    protected $description = 'Create Migrations/Models/Repositories';

    /** @var \TakaakiMizuno\MWBParser\Elements\Table[] $tables */
    protected $tables;

    /** @var \LaravelRocket\Generator\Objects\Definitions */
    protected $json;

    /** @var DatabaseService $databaseService */
    protected $databaseService;

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

        $success = $this->validateTableSchema();
        if (!$success) {
            return false;
        }

        $this->databaseService = new DatabaseService($this->config, $this->files);
        $databaseName          = $this->databaseService->resetDatabase();

        $this->generateMigration();
        $this->generateModel();

        $this->databaseService->dropDatabase();
    }

    protected function getTablesFromMWBFile()
    {
        $file    = $this->option('file');
        $default = false;
        if (empty($file)) {
            $default = true;
            $file    = base_path('documents/db.mwb');
        }

        if (!file_exists($file)) {
            if ($default) {
                $this->output('File ( '.$file.' ) doesn\'t exist. This is default file path. You can specify file path with --file option.', 'error');
            } else {
                $this->output('File ( '.$file.' ) doesn\'t exist. Please check file path.', 'error');
            }

            return false;
        }

        $parser = new Parser($file);
        $tables = $parser->getTables();
        if (is_null($tables)) {
            $this->output('File ( '.$file.' ) is not MWB format', 'error');

            return false;
        }
        if (count($tables) === 0) {
            $this->output('File ( '.$file.' ) doesn\'t include any table.', 'error');

            return false;
        }

        return $tables;
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

    protected function generateMigration()
    {
        $generator = new MigrationFileGenerator($this->config, $this->files, $this->view);
        foreach ($this->tables as $table) {
            $generator->generate($table);
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
