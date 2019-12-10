<?php
namespace LaravelRocket\Generator\Generators\Events;

use Illuminate\Support\Str;
use LaravelRocket\Generator\Generators\NameBaseGenerator;

class ListenerGenerator extends NameBaseGenerator
{
    protected function canGenerate(): bool
    {
        return $this->rebuild || !file_exists($this->getPath());
    }

    protected function normalizeName(string $name): string
    {
        return ucfirst(Str::camel($name));
    }

    protected function getModels()
    {
        $directory = app_path('Models');
        if (!is_dir($directory)) {
            return [];
        }
        $models = [];
        $files  = scandir($directory);
        foreach ($files as $file) {
            if (in_array($file, ['..', '.'])) {
                continue;
            }
            $modelName = pathinfo($file, PATHINFO_FILENAME);
            $models[]  = $modelName;
        }

        return $models;
    }

    protected function getServices()
    {
        $directory = app_path('Services'.DIRECTORY_SEPARATOR.'Production');
        if (!is_dir($directory)) {
            return [];
        }
        $services = [];
        $files    = scandir($directory);
        foreach ($files as $file) {
            if (in_array($file, ['..', '.'])) {
                continue;
            }
            $serviceName = pathinfo($file, PATHINFO_FILENAME);
            $services[]  = substr($serviceName, 0, strlen($serviceName) - 7);
        }

        return $services;
    }

    protected function getRelatedModels()
    {
        $relatedModels = [];
        $models        = $this->getModels();
        foreach ($models as $model) {
            if (Str::startsWith($this->name, $model)) {
                $relatedModels[] = $model;
            }
        }

        return $relatedModels;
    }

    protected function getRelatedServices()
    {
        $relatedServices = [];
        $services        = $this->getServices();
        foreach ($services as $service) {
            if (Str::startsWith($this->name, $service)) {
                $relatedServices[] = $service;
            }
        }

        return $relatedServices;
    }

    /**
     * @return array
     */
    protected function getVariables(): array
    {
        $eventName = $this->name;
        $variables = [
            'eventName'    => $eventName,
            'listenerName' => $eventName.'EventListener',
            'models'       => $this->getRelatedModels(),
            'services'     => $this->getRelatedServices(),
        ];

        return $variables;
    }

    /**
     * @return string
     */
    protected function getPath(): string
    {
        return app_path('Listeners/'.$this->name.'EventListener.php');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return 'event.listener';
    }
}
