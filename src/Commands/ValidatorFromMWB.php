<?php
namespace LaravelRocket\Generator\Commands;

use LaravelRocket\Generator\Validators\Error;
use LaravelRocket\Generator\Validators\TableSchemaValidator;
use TakaakiMizuno\MWBParser\Parser;

class ValidatorFromMWB extends BaseCommand
{
    protected $name = 'rocket:validate:from-mwb';

    protected $signature = 'rocket:validate:from-mwb {--file=} {--json=}';

    protected $description = 'Validate MySQL Workbench file';

    /** @var \TakaakiMizuno\MWBParser\Elements\Table[] $tables */
    protected $tables;

    /** @var \LaravelRocket\Generator\Objects\Definitions */
    protected $json;

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

        return true;
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
}
