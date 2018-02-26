<?php
namespace LaravelRocket\Generator\FileUpdaters\Events;

use LaravelRocket\Generator\FileUpdaters\NameBaseFileUpdater;

class RegisterEventFileUpdater extends NameBaseFileUpdater
{
    protected function normalizeName(string $name): string
    {
        return ucfirst(camel_case($name));
    }

    protected function getTargetFilePath(): string
    {
        return app_path('Providers/EventServiceProvider.php');
    }

    /**
     * @return int
     */
    protected function getInsertPosition(): int
    {
        return $this->getEndOfProperty($this->getTargetFilePath(), 'listen');
    }

    /**
     * @return int
     */
    protected function getExistingPosition(): int
    {
        $lines = file($this->getTargetFilePath());
        if ($lines === false) {
            return -1;
        }

        foreach ($lines as $index => $line) {
            if (strpos($line, $this->name.'::class') !== false) {
                return $index + 1;
            }
        }

        return -1;
    }

    /**
     * @return string
     */
    protected function getInsertData(): string
    {
        return <<< EOS
        \App\Events\\{$this->name}::class => [
            \App\Listeners\\{$this->name}EventListener::class,
        ],

EOS;
    }
}
