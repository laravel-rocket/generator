<?php
namespace LaravelRocket\Generator\FileUpdaters;

class OpenAPIBaseFileUpdater extends BaseFileUpdater
{
    protected $excludePostfixes = ['password_resets'];

    /** @var string */
    protected $name;

    /** @var \LaravelRocket\Generator\Objects\OpenAPI\OpenAPISpec */
    protected $spec;

    /** @var \LaravelRocket\Generator\Objects\Definitions */
    protected $json;

    /** @var string */
    protected $versionNamespace;

    /**
     * @param string                                               $name
     * @param \LaravelRocket\Generator\Objects\OpenAPI\OpenAPISpec $spec
     * @param \LaravelRocket\Generator\Objects\Definitions         $json
     *
     * @return bool
     */
    public function insert($name, $spec, $json): bool
    {
        $this->name = $name;
        $this->spec = $spec;
        $this->json = $json;

        $this->setVersion();
        $filePath = $this->getTargetFilePath();

        $this->preprocess();

        if (!$this->needGenerate()) {
            return false;
        }

        $insertPosition = $this->getInsertPosition();
        $data           = $this->getInsertData();

        return $this->insertDataToLine($data, $filePath, $insertPosition);
    }

    protected function preprocess()
    {
    }

    /**
     * @return bool
     */
    protected function needGenerate()
    {
        $existingPosition = $this->getExistingPosition();
        if ($existingPosition >= 0) {
            return false;
        }

        return true;
    }

    protected function setVersion()
    {
        $this->versionNamespace = $this->spec->getVersionNamespace();
    }
}
