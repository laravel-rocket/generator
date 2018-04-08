<?php
namespace LaravelRocket\Generator\Commands;

use LaravelRocket\Generator\FileUpdaters\APIs\OpenAPI\RouterFileUpdater;
use LaravelRocket\Generator\Services\DatabaseService;
use LaravelRocket\Generator\Services\OASService;
use LaravelRocket\Generator\Validators\APIs\APIValidator;
use LaravelRocket\Generator\Validators\Error;

class GenerateAPIFromOAS extends MWBGenerator
{
    protected $name = 'rocket:generate:api:from-oas';

    protected $signature = 'rocket:generate:api:from-oas {--rebuild} {--osa=} {--file=} {--json=}';

    protected $description = 'Create API from OAS file';

    /** @var \LaravelRocket\Generator\Objects\OpenAPI\OpenAPISpec $spec */
    protected $spec;

    /** @var DatabaseService $databaseService */
    protected $databaseService;

    /** @var bool */
    protected $rebuild = false;

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        $this->rebuild = !empty($this->input->getOption('rebuild'));

        $this->tables = $this->getTablesFromMWBFile();
        if ($this->tables === false) {
            return false;
        }

        $this->getAppJson();

        $this->spec = $this->getAPISpecFromOASFile();
        if ($this->spec === null) {
            return false;
        }

        $success = $this->validateAPIDefinition();
        if (!$success) {
            return false;
        }

        $this->databaseService = new DatabaseService($this->config, $this->files);
        $this->databaseService->resetDatabase();

        $this->generateFromDefinitions();
        $this->generateControllers();
        $this->generateRequests();
        $this->insertRoutes();
        $this->styleCode();

        $this->databaseService->dropDatabase();

        return true;
    }

    /**
     * @return bool|\LaravelRocket\Generator\Objects\OpenAPI\OpenAPISpec
     */
    protected function getAPISpecFromOASFile()
    {
        $file    = $this->option('osa');
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
        $oas    = $parser->parse($file, $this->tables, $this->json);
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
        list($success, $errors) = $validator->validate($this->spec, $this->json);

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
            new \LaravelRocket\Generator\Generators\APIs\OpenAPI\ResponseGenerator($this->config, $this->files, $this->view, $this->rebuild),
        ];

        foreach ($this->spec->getDefinitions() as $definition) {
            foreach ($generators as $generator) {
                $generator->generate($definition->getName(), $this->spec, $this->databaseService, $this->json, $this->tables);
            }
        }
    }

    protected function generateControllers()
    {
        /** @var \LaravelRocket\Generator\Generators\APIBaseGenerator[] $generators */
        $generators = [
            new \LaravelRocket\Generator\Generators\APIs\OpenAPI\ControllerGenerator($this->config, $this->files, $this->view, $this->rebuild),
        ];

        foreach ($this->spec->getControllers() as $controller) {
            foreach ($generators as $generator) {
                $generator->generate($controller->getName(), $this->spec, $this->databaseService, $this->json, $this->tables);
            }
        }
    }

    protected function generateRequests()
    {
        /** @var \LaravelRocket\Generator\Generators\APIBaseGenerator[] $generators */
        $generators = [
            new \LaravelRocket\Generator\Generators\APIs\OpenAPI\RequestGenerator($this->config, $this->files, $this->view, $this->rebuild),
        ];

        foreach ($this->spec->getControllers() as $controller) {
            foreach ($controller->getRequiredRequestNames() as $requestName) {
                foreach ($generators as $generator) {
                    $generator->generate($requestName, $this->spec, $this->databaseService, $this->json, $this->tables);
                }
            }
        }
    }

    protected function insertRoutes()
    {
        $fileUpdaters = [
            new RouterFileUpdater($this->config, $this->files, $this->view, $this->rebuild),
        ];

        foreach ($this->spec->getActions() as $action) {
            foreach ($fileUpdaters as $generator) {
                $generator->generate($action->getPath(), $this->spec, $this->databaseService, $this->json, $this->tables);
            }
        }
    }
}
