<?php
namespace LaravelRocket\Generator\Commands;

use LaravelRocket\Generator\FileUpdaters\Services\RegisterServiceFileUpdater;

class ServiceGenerator extends BaseCommand
{
    protected $name = 'rocket:make:service';

    protected $signature = 'rocket:make:service {$name}';

    protected $description = 'Create Service';

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        $this->generateService();

        return true;
    }

    protected function generateService()
    {
        /** @var \LaravelRocket\Generator\Generators\NameBaseGenerator[] $generators */
        $generators = [
            new \LaravelRocket\Generator\Generators\Services\ServiceGenerator($this->config, $this->files, $this->view),
            new \LaravelRocket\Generator\Generators\Services\ServiceInterfaceGenerator($this->config, $this->files, $this->view),
            new \LaravelRocket\Generator\Generators\Services\ServiceUnitTestGenerator($this->config, $this->files, $this->view),
        ];

        /** @var \LaravelRocket\Generator\FileUpdaters\NameBaseFileUpdater[] $fileUpdaters */
        $fileUpdaters = [
            new RegisterServiceFileUpdater($this->config, $this->files, $this->view),
        ];

        $this->output('Processing '.$this->name.'Service...', 'green');
        foreach ($generators as $generator) {
            $generator->generate($this->name, []);
        }
        foreach ($fileUpdaters as $fileUpdater) {
            $fileUpdater->insert($this->name);
        }
    }
}
