<?php

namespace LaravelRocket\Generator\Commands;

use LaravelRocket\Generator\Validators\Error;
use LaravelRocket\Generator\Validators\Services\ServiceValidator;

class Validate extends BaseCommand
{
    protected $name = 'rocket:validate';

    protected $signature = 'rocket:validate {--json=}';

    protected $description = 'Validate Full Code';

    /** @var \LaravelRocket\Generator\Objects\Definitions */
    protected $json;

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        $this->getAppJson();

        $success = $this->validateService();
        if (!$success) {
            return false;
        }

        return true;
    }

    protected function validateService()
    {
        $validator = new ServiceValidator($this->config, $this->files, $this->view);

        /** @var bool $success */
        /** @var \LaravelRocket\Generator\Validators\Error[] $errors */
        list($success, $errors) = $validator->validate($this->json);

        $this->output('Service Validation Result');
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
