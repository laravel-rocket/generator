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

    public function insert($name): bool
    {
        $this->name = $this->normalizeName($name);

        $filePath = $this->getTargetFilePath();

        $existingPosition = $this->getExistingPosition();
        if ($existingPosition >= 0) {
            return false;
        }

        $insertPosition = $this->getInsertPosition();
        $data           = $this->getInsertData();

        return $this->insertDataToLine($data, $filePath, $insertPosition);
    }
}
