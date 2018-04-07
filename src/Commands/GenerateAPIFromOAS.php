<?php
namespace LaravelRocket\Generator\Commands;

use LaravelRocket\Generator\Objects\OpenAPI\Path;
use LaravelRocket\Generator\Services\DatabaseService;
use LaravelRocket\Generator\Services\OASService;
use LaravelRocket\Generator\Validators\APIs\APIValidator;
use LaravelRocket\Generator\Validators\Error;

class GenerateAPIFromOAS extends MWBGenerator
{
    protected $name = 'rocket:generate:api:from-oas';

    protected $signature = 'rocket:generate:api:from-oas {--rebuild} {--osa=} {--file=} {--json=}';

    protected $description = 'Create API from OAS file';

    protected $oas;

    protected $controllers = [];

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

        $this->reorganizePath();
        $this->generateFromDefinitions();
        $this->generateControllers();
        $this->styleCode();

        $this->databaseService->dropDatabase();

        return true;
    }

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
            new \LaravelRocket\Generator\Generators\APIs\OpenAPI\ResponseGenerator($this->config, $this->files, $this->view),
        ];

        $definitions = $this->oas->definitions;
        foreach ($definitions as $name => $definition) {
            foreach ($generators as $generator) {
                $generator->generate($name, null, $definition, $this->oas, $this->databaseService, $this->json, $this->tables);
            }
        }
    }

    protected function generateControllers()
    {
        /** @var \LaravelRocket\Generator\Generators\APIBaseGenerator[] $generators */
        $generators = [
            new \LaravelRocket\Generator\Generators\APIs\OpenAPI\ControllerGenerator($this->config, $this->files, $this->view),
        ];

        foreach ($this->controllers as $name => $definition) {
            foreach ($generators as $generator) {
                $generator->generate($name, $definition, null, $this->oas, $this->databaseService, $this->json, $this->tables);
            }
        }
    }

    protected function reorganizePath()
    {
        $this->controllers = [];
        $paths             = $definitions = $this->oas->paths;
        foreach ($paths as $path => $pathInfo) {
            $methods = $pathInfo->getMethods();
            foreach ($methods as $method => $info) {
                $pathObject = new Path($path, $method, $info);
                foreach ($pathObject->getActions() as $action) {
                    if (!$this->checkActionAlreadyExists($action)) {
                        if (!array_key_exists($action->getController(), $this->controllers)) {
                            $this->controllers[$action->getController()] = [];
                        }
                        $this->controllers[$action->getController()][$action->getMethod()] = $action;
                        break;
                    }
                }
            }
        }
    }

    /**
     * @param \LaravelRocket\Generator\Objects\OpenAPI\Action $action
     *
     * @return bool
     */
    protected function checkActionAlreadyExists($action)
    {
        $controllerName = $action->getController();
        if (!array_key_exists($controllerName, $this->controllers)) {
            return false;
        }

        if (!array_key_exists($action->getMethod(), $this->controllers[$controllerName])) {
            return false;
        }

        return true;
    }
}
