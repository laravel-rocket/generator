<?php
namespace LaravelRocket\Generator\FileUpdaters\CRUD\Admin;

use LaravelRocket\Generator\FileUpdaters\TableBaseFileUpdater;
use function ICanBoogie\pluralize;

class SideBarFileUpdater extends TableBaseFileUpdater
{
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
                return $index;
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
                return $index;
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
        $title     = ucfirst(pluralize($this->getModelName()));
        $keyName   = kebab_case($this->getModelName());

        return <<< EOS
            <li @if( \$menu=='$keyName') class="active" @endif >
                <a href="{!! action('Admin\{$modelName}Controller@index') !!\}">
                    <i class="fa fa-users"></i>
                    <span>$title</span>
                </a>
            </li>

EOS;
    }
}
