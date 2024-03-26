<?php

namespace LaravelRocket\Generator\Commands;

use TakaakiMizuno\MWBParser\Parser;

class MWBGenerator extends BaseCommand
{
    /** @var \TakaakiMizuno\MWBParser\Elements\Table[] $tables */
    protected $tables;

    /** @var \LaravelRocket\Generator\Objects\Definitions */
    protected $json;

    /** @var \LaravelRocket\Generator\Services\DatabaseService $databaseService */
    protected $databaseService;

    /**
     * @return bool|\TakaakiMizuno\MWBParser\Elements\Table[]
     */
    protected function getTablesFromMWBFile()
    {
        $file    = $this->option('file');
        $default = false;
        if (empty($file)) {
            $default = true;
            $file    = base_path('documents/db.mwb');
        }

        if (!file_exists($file)) {
            if ($default) {
                $this->output('File ( '.$file.' ) doesn\'t exist. This is default file path. You can specify file path with --file option.', 'error');
            } else {
                $this->output('File ( '.$file.' ) doesn\'t exist. Please check file path.', 'error');
            }

            return false;
        }

        $parser = new Parser($file);
        $tables = $parser->getTables();
        if (is_null($tables)) {
            $this->output('File ( '.$file.' ) is not MWB format', 'error');

            return false;
        }
        if (count($tables) === 0) {
            $this->output('File ( '.$file.' ) doesn\'t include any table.', 'error');

            return false;
        }

        return $tables;
    }

    /**
     * @param string $name
     *
     * @return null|\TakaakiMizuno\MWBParser\Elements\Table
     */
    protected function findTableFromName($name)
    {
        foreach ($this->tables as $table) {
            if ($table->getName() === $name) {
                return $table;
            }
        }

        return null;
    }
}
