<?php
namespace LaravelRocket\Generator\FileUpdaters\Models;

use LaravelRocket\Generator\FileUpdaters\TableBaseFileUpdater;

class RegisterRepositoryFileUpdater extends TableBaseFileUpdater
{
    public function needGenerate()
    {
        return true;
    }

    protected function getTargetFilePath(): string
    {
        return app_path('Providers/RepositoryServiceProvider.php');
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
        $modelName = $this->getModelName();

        $lines = file($this->getTargetFilePath());
        if ($lines === false) {
            return -1;
        }

        foreach ($lines as $index => $line) {
            if (strpos($line, '\\'.$modelName.'RepositoryInterface::class') !== false) {
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
        $modelName = $this->getModelName();

        return <<< EOS

        \$this->app->singleton(
            \\App\\Repositories\\{$modelName}RepositoryInterface::class,
            \\App\\Repositories\\Eloquent\\{$modelName}Repository::class
        );

EOS;
    }
}
