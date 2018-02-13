<?php
namespace LaravelRocket\Generator\Commands;

use LaravelRocket\Generator\Generators\APIs\ResponseGenerator;
use LaravelRocket\Generator\Services\DatabaseService;
use LaravelRocket\Generator\Services\OASService;
use LaravelRocket\Generator\Validators\APIValidator;
use LaravelRocket\Generator\Validators\Error;

class GenerateAPIFromOAS extends BaseCommand
{
    protected $name = 'rocket:generate:api:from-oas';

    protected $signature = 'rocket:generate:api:from-oas {--file=} {--json=}';

    protected $description = 'Create API from OAS file';

    protected $oas;

    /** @var DatabaseService $databaseService */
    protected $databaseService;

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        $this->oas = $this->getAPISpecFromOASFile();
        if ($this->oas === false) {
            return false;
        }
        $this->getAppJson();

        $success = $this->validateAPIDefinition();
        if (!$success) {
            return false;
        }

        $this->databaseService = new DatabaseService($this->config, $this->files);
        $this->databaseService->resetDatabase();

        $this->databaseService->dropDatabase();

        return true;
    }

    protected function getAPISpecFromOASFile()
    {
        $file    = $this->option('file');
        $default = false;
        if (empty($file)) {
            $default = true;
            $file    = base_path('documents/api.yaml');
        }

        if (!file_exists($file)) {
            if ($default) {
                $this->output('File ( '.$file.' ) doesn\'t exist. This is default file path. You can specify file path with --file option.', 'error');
            } else {
                $this->output('File ( '.$file.' ) doesn\'t exist. Please check file path.', 'error');
            }

            return false;
        }

        $parser = new OASService();
        $oas    = $parser->parse($file);
        if (empty($oas)) {
            return false;
        }

        return $oas;
    }

    protected function validateAPIDefinition()
    {
        $validator = new APIValidator($this->config, $this->files, $this->view);

        /** @var bool $success */
        /** @var \LaravelRocket\Generator\Validators\Error[] $errors */
        list($success, $errors) = $validator->validate($this->oas, $this->json);

        $this->output('API Validation Result');
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

    protected function generateFromDefinitions()
    {
        /** @var \LaravelRocket\Generator\Generators\APIBaseGenerator[] $generators */
        $generators = [
            new ResponseGenerator($this->config, $this->files, $this->view),
        ];

        $definitions = $this->oas->definitions;
        foreach ($definitions as $name => $definition) {
            foreach ($generators as $generator) {
                $generator->generate($name, $definition, $this->oas, $this->databaseService, $this->json);
            }
        }
    }
}
