<?php

namespace LaravelRocket\Generator\FileUpdaters\Helpers;

use Illuminate\Support\Str;
use LaravelRocket\Generator\FileUpdaters\NameBaseFileUpdater;

use function ICanBoogie\singularize;

class AppConfigFileUpdater extends NameBaseFileUpdater
{
    protected function normalizeName(string $name): string
    {
        if (Str::endsWith($name, 'Helper')) {
            $name = substr($name, 0, strlen($name) - 7);
        }

        return ucfirst(Str::camel(singularize($name)));
    }

    protected function getTargetFilePath(): string
    {
        return config_path('app.php');
    }

    /**
     * @return int
     */
    protected function getInsertPosition(): int
    {
        return $this->getEndOfArrayItemArray($this->getTargetFilePath(), 'aliases');
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
            if (strpos($line, $this->name.'HelperInterface::class') !== false) {
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
        '{$this->name}Helper'     => App\Facades\\{$this->name}Helper::class,
EOS;
    }
}
