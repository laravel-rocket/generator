<?php
namespace LaravelRocket\Generator\FileUpdaters\APIs\OpenAPI;

use LaravelRocket\Generator\FileUpdaters\OpenAPIBaseFileUpdater;

class RouterFileUpdater extends OpenAPIBaseFileUpdater
{
    /** @var \LaravelRocket\Generator\Objects\OpenAPI\Action */
    protected $action;

    protected function preprocess()
    {
        $this->action = $this->spec->findAction($this->name);

        $filePath = $this->getTargetFilePath();
        if (!file_exists($filePath)) {
            $this->fileService->render('api.oas.router', $filePath, [
                'versionNamespace' => $this->spec->getVersionNamespace(),
            ]);
        }
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

        $searchString = $this->action->getRouteName();

        foreach ($lines as $index => $line) {
            if (strpos($line, $this->action->getHttpMethod().'(\''.$searchString.'\'', $line)) {
                return $index + 1;
            }
        }

        return -1;
    }

    protected function getTargetFilePath(): string
    {
        return base_path('routes/api/'.$this->versionNamespace.'.php');
    }

    /**
     * @return int
     */
    protected function getInsertPosition(): int
    {
        $lines = file($this->getTargetFilePath());
        if ($lines === false) {
            return -1;
        }

        $indent = 0;
        foreach ($lines as $index => $line) {
            if (strpos($line, 'Route::group') !== false) {
                $indent = $indent + 1;
            }
            if (strpos($line, '});') !== false) {
                $indent = $indent - 1;
                if ($indent == 1) {
                    return $index + 1;
                }
            }
        }

        return -1;
    }

    /**
     * @return string
     */
    protected function getInsertData(): string
    {
        $routeName  = $this->action->getRouteName();
        $pathName   = $this->action->getPath();
        $identifier = $this->action->getRouteIdentifier();
        $httpMethod = $this->action->getHttpMethod();

        return <<< EOS
        Route::$httpMethod('$pathName', '$routeName')->name('$identifier');

EOS;
    }
}
