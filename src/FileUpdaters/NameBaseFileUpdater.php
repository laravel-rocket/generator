<?php
namespace LaravelRocket\Generator\FileUpdaters;

use function ICanBoogie\singularize;

class NameBaseFileUpdater extends BaseFileUpdater
{
    /**
     * @var string
     */
    protected $name = '';

    protected function normalizeName(string $name): string
    {
        return ucfirst(camel_case(singularize($name)));
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

    protected function preprocess()
    {
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function insert(string $name): bool
    {
        $this->name = $this->normalizeName($name);

        $this->preprocess();

        $filePath = $this->getTargetFilePath();

        if (!$this->needGenerate()) {
            return false;
        }

        $insertPosition = $this->getInsertPosition();
        $data           = $this->getInsertData();

        return $this->insertDataToLine($data, $filePath, $insertPosition);
    }
}
