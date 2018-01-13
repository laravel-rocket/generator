<?php
namespace LaravelRocket\Generator\FileUpdaters\CRUD\Admin;

use LaravelRocket\Generator\FileUpdaters\TableBaseFileUpdater;
use function ICanBoogie\pluralize;

class SideBarFileUpdater extends TableBaseFileUpdater
{
    public function needGenerate()
    {
        foreach ($this->excludePostfixes as $excludePostfix) {
            if (ends_with($this->table->getName(), $excludePostfix)) {
                return false;
            }
        }

        return !$this->detectRelationTable($this->table);
    }

    protected function getTargetFilePath(): string
    {
        return resource_path('views/shared/admin/side_menu.blade.php');
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

        foreach ($lines as $index => $line) {
            if (strpos($line, '</ul>') !== false) {
                return $index + 1;
            }
        }

        return -1;
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
            if (strpos($line, $modelName.'Controller') !== false) {
                return $index + 1;
            }
        }

        return -1;
    }

    protected function getFontAwesomeIcon()
    {
        $mappings = [
            'users'     => 'fa-users',
            'images'    => 'fa-images',
            'companies' => 'fa-building',

        ];

        $name = $this->table->getName();
        foreach ($mappings as $mapping => $iconClass) {
            if (ends_with($name, $mapping)) {
                return $iconClass;
            }
        }

        return 'fa-files';
    }

    /**
     * @return string
     */
    protected function getInsertData(): string
    {
        $controllerName = $this->getModelName().'Controller';
        $title          = ucfirst(pluralize($this->getModelName()));
        $keyName        = kebab_case(camel_case($this->getModelName()));
        $iconClass      = $this->getFontAwesomeIcon();

        return <<< EOS
            <li @if( \$menu=='$keyName') class="active" @endif >
                <a href="{!! action('Admin\\$controllerName@index') !!}">
                    <i class="fa $iconClass"></i>
                    <span>$title</span>
                </a>
            </li>

EOS;
    }
}
