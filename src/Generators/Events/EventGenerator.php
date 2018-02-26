<?php
namespace LaravelRocket\Generator\Generators\Events;

use LaravelRocket\Generator\Generators\NameBaseGenerator;

class EventGenerator extends NameBaseGenerator
{
    protected function canGenerate(): bool
    {
        return $this->rebuild || !file_exists($this->getPath());
    }

    protected function normalizeName(string $name): string
    {
        return ucfirst(camel_case($name));
    }

    protected function getModels()
    {
        $directory = app_path('Models');
        if (!file_exists($directory) || is_dir($directory)) {
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

    protected function getRelatedModels()
    {
        $relatedModels = [];
        $models        = $this->getModels();
        foreach ($models as $model) {
            if (starts_with($this->name, $model)) {
                $relatedModels[] = $model;
            }
        }

        return $relatedModels;
    }

    /**
     * @return array
     */
    protected function getVariables(): array
    {
        $eventName = $this->name;
        $variables = [
            'eventName' => $eventName,
            'models'    => $this->getRelatedModels(),
        ];

        return $variables;
    }

    /**
     * @return string
     */
    protected function getPath(): string
    {
        return app_path('Events/'.$this->name.'.php');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return 'event.event';
    }
}
