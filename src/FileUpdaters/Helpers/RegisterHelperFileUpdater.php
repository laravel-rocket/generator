<?php
namespace LaravelRocket\Generator\FileUpdaters\Helpers;

use Illuminate\Support\Str;
use LaravelRocket\Generator\FileUpdaters\NameBaseFileUpdater;
use function ICanBoogie\singularize;

class RegisterHelperFileUpdater extends NameBaseFileUpdater
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
        return app_path('Providers/HelperServiceProvider.php');
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
            if (strpos($line, $this->name.'HelperInterface::class') !== false) {
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
        return <<< EOS

        \$this->app->singleton(
            \\App\\Helpers\\{$this->name}HelperInterface::class,
            \\App\\Helpers\\Production\\{$this->name}Helper::class
        );

EOS;
    }
}
