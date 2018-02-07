<?php
namespace LaravelRocket\Generator\Commands;

class HelperGenerator extends BaseCommand
{
    protected $name = 'rocket:make:helper';

    protected $signature = 'rocket:make:helper {$name}';

    protected $description = 'Create Helper';

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        $this->generateHelper();

        return true;
    }

    protected function generateHelper()
    {
        /** @var \LaravelRocket\Generator\Generators\NameBaseGenerator[] $generators */
        $generators = [
            new \LaravelRocket\Generator\Generators\Helpers\HelperGenerator($this->config, $this->files, $this->view),
            new \LaravelRocket\Generator\Generators\Helpers\HelperInterfaceGenerator($this->config, $this->files, $this->view),
            new \LaravelRocket\Generator\Generators\Helpers\HelperUnitTestGenerator($this->config, $this->files, $this->view),
        ];

        /** @var \LaravelRocket\Generator\FileUpdaters\NameBaseFileUpdater[] $fileUpdaters */
        $fileUpdaters = [
            new RegisterHelperFileUpdater($this->config, $this->files, $this->view),
        ];

        $this->output('Processing '.$this->name.'Helper...', 'green');
        foreach ($generators as $generator) {
            $generator->generate($this->name);
        }
        foreach ($fileUpdaters as $fileUpdater) {
            $fileUpdater->insert($this->name);
        }
    }
}
