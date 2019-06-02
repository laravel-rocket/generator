<?php
namespace LaravelRocket\Generator\FileUpdaters\Tests;

use function ICanBoogie\singularize;
use Illuminate\Support\Str;
use LaravelRocket\Generator\FileUpdaters\NameBaseFileUpdater;

class ServiceTestFileUpdater extends NameBaseFileUpdater
{
    protected function normalizeName(string $name): string
    {
        if (Str::endsWith($name, 'Service')) {
            $name = substr($name, 0, strlen($name) - 7);
        }

        return ucfirst(Str::camel(singularize($name)));
    }

    protected function getTargetFilePath(): string
    {
        return base_path('tests/Services/'.$this->name.'ServiceTest.php');
    }

    /**
     * @return string[]
     */
    protected function getAllServiceMethods(): array
    {
        return [];
    }

    /**
     * @return int
     */
    protected function getInsertPosition(): int
    {
        return $this->getEndOfMethodPosition($this->getTargetFilePath(), 'register');
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
            if (strpos($line, $this->name.'ServiceInterface::class') !== false) {
                if (strpos($line, 'singleton') !== false) {
                    return $index + 1;
                }

                return $index - 1;
            }
        }

        return -1;
    }

    /**
     * @return string
     */
    protected function getInsertData(): string
    {
        return '';
    }
}
