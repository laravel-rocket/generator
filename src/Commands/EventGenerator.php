<?php

namespace LaravelRocket\Generator\Commands;

use Illuminate\Support\Str;
use LaravelRocket\Generator\FileUpdaters\Events\RegisterEventFileUpdater;
use LaravelRocket\Generator\Generators\Events\ListenerGenerator;
use LaravelRocket\Generator\Generators\Events\UnitTestGenerator;

class EventGenerator extends BaseCommand
{
    protected $name = 'rocket:make:event';

    protected $signature = 'rocket:make:event {name} {--rebuild} {--json=}';

    protected $description = 'Create Event & Listener';

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        $this->getAppJson();
        $this->generateService();

        return true;
    }

    protected function normalizeName(string $name): string
    {
        return ucfirst(Str::camel($name));
    }

    protected function generateService()
    {
        $rebuild = !empty($this->input->getOption('rebuild'));

        /** @var \LaravelRocket\Generator\Generators\NameBaseGenerator[] $generators */
        $generators = [
            new \LaravelRocket\Generator\Generators\Events\EventGenerator($this->config, $this->files, $this->view, $this->json, $rebuild),
            new ListenerGenerator($this->config, $this->files, $this->view, $this->json, $rebuild),
            new UnitTestGenerator($this->config, $this->files, $this->view, $this->json, $rebuild),
        ];

        /** @var \LaravelRocket\Generator\FileUpdaters\NameBaseFileUpdater[] $fileUpdaters */
        $fileUpdaters = [
            new RegisterEventFileUpdater($this->config, $this->files, $this->view, $rebuild),
        ];

        $name = $this->normalizeName($this->argument('name'));

        $this->output('Processing Event Named '.$name.'...', 'green');
        foreach ($generators as $generator) {
            $generator->generate($name, $this->json);
        }
        foreach ($fileUpdaters as $fileUpdater) {
            $fileUpdater->insert($name);
        }
    }
}
