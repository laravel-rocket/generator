<?php

namespace LaravelRocket\Generator\FileUpdaters\React\CRUD\Admin;

use Illuminate\Support\Str;
use LaravelRocket\Generator\FileUpdaters\TableBaseFileUpdater;

class ReactCRUDAdminFileUpdater extends TableBaseFileUpdater
{
    protected $excludePostfixes = ['password_resets', 'roles'];

    public function needGenerate()
    {
        foreach ($this->excludePostfixes as $excludePostfix) {
            if (Str::endsWith($this->table->getName(), $excludePostfix)) {
                return false;
            }
        }

        $excludes = $this->json->get('admin.cruds.exclude', []);
        if (in_array($this->table->getName(), $excludes)) {
            return false;
        }

        return !$this->detectRelationTable($this->table);
    }
}
