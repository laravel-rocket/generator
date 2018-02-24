<?php
namespace LaravelRocket\Generator\Generators\Migrations;

use Carbon\Carbon;
use LaravelRocket\Generator\Generators\BaseGenerator;
use LaravelRocket\Generator\Objects\Column;
use LaravelRocket\Generator\Objects\Index;
use LaravelRocket\Generator\Services\DatabaseService;
use LaravelRocket\Generator\Services\FileService;
use TakaakiMizuno\MWBParser\Elements\Table;

class MigrationFileGenerator extends BaseGenerator
{
    /**
     * @var bool
     */
    protected $generateAlterTableMigrationFile = false;

    /**
     * @param Table $table
     * @param bool  $generateAlterTableMigrationFile
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return bool
     */
    public function generate($table, $generateAlterTableMigrationFile = false)
    {
        $this->generateAlterTableMigrationFile = $generateAlterTableMigrationFile;

        $dateTime = Carbon::now();

        $existingPath     = $this->findExistingCreateMigrationFile($table);
        $isAlterMigration = false;

        if ($this->generateAlterTableMigrationFile) {
            if (!empty($existingPath)) {
                $filePath         = $this->getMigrationPath($table->getName(), $dateTime, true);
                $isAlterMigration = true;
            } else {
                $filePath = $this->getPath($table->getName(), $dateTime, false);
            }
        } else {
            if (!empty($existingPath)) {
                $filePath = $existingPath;
            } else {
                $filePath = $this->getPath($table->getName(), $dateTime, false);
            }
        }

        if ($isAlterMigration) {
            $result              = $this->getAlterTableInfo($table);
            if (!$result) {
                return false;
            }
        } else {
            $result              = $this->generateColumns($table);
        }
        $result['tableName'] = $table->getName();
        $result['indexes']   = $this->generateIndexes($table);
        $result['className'] = $this->getClassName($table->getName(), $dateTime, $isAlterMigration);

        $template = $isAlterMigration ? 'migration.alter' : 'migration.create';

        /* @var FileService $fileService */
        $this->fileService->render($template, $filePath, $result, true);

        return true;
    }

    /**
     * @return string
     */
    protected function getMigrationBasePath(): string
    {
        $basePath = database_path('migrations');

        return $basePath;
    }

    /**
     * @param Table $table
     *
     * @return string|null
     */
    protected function findExistingCreateMigrationFile($table)
    {
        $directory = database_path('migrations');
        $files     = scandir($directory);
        $basePath  = $this->getMigrationBasePath();

        foreach ($files as $file) {
            if (strpos($file, 'create_'.$table->getName().'_table') !== false) {
                return $basePath.DIRECTORY_SEPARATOR.$file;
            }
        }

        return null;
    }

    /**
     * @param string $name
     * @param Carbon $dateTime
     * @param bool   $isAlterMigration
     *
     * @return string
     */
    protected function getClassName($name, $dateTime, $isAlterMigration = false): string
    {
        if ($isAlterMigration) {
            return 'Alter'.ucfirst(camel_case($name)).'Table'.$dateTime->format('Y_m_d_His');
        }

        return 'Create'.ucfirst(camel_case($name)).'Table';
    }

    /**
     * @param string $name
     * @param Carbon $dateTime
     * @param bool   $isAlterMigration
     *
     * @return string
     */
    protected function getMigrationPath($name, $dateTime, $isAlterMigration = false)
    {
        $basePath = $this->getMigrationBasePath();

        $type = $isAlterMigration ? 'alter' : 'create';

        return $basePath.DIRECTORY_SEPARATOR.$dateTime->format('Y_m_d_His').'_'.$type.'_'.$name.'_table.php';
    }

    /**
     * @param Table $table
     *
     * @return array
     */
    protected function generateColumns($table): array
    {
        $columns = $table->getColumns();
        $result  = [
            'columns'          => [],
            'hasSoftDelete'    => false,
            'hasRememberToken' => false,
        ];
        foreach ($columns as $column) {
            if ($column->getName() === 'id') {
                continue;
            }
            if (($column->getName() === 'created_at' || $column->getName() === 'updated_at') and ($column->getType() === 'timestamp' || $column->getType() === 'timestamp_f')) {
                continue;
            }
            if ($column->getName() === 'deleted_at' and ($column->getType() === 'timestamp' || $column->getType() === 'timestamp_f')) {
                $result['hasSoftDelete'] = true;
                continue;
            }
            if ($column->getName() === 'remember_token' and $column->getType() === 'varchar') {
                $result['hasRememberToken'] = true;
                continue;
            }

            $columnObject = new Column($column);
            $line         = $columnObject->generateAddMigration();

            $result['columns'][] = $line.';';
        }

        return $result;
    }

    /**
     * @param Table $table
     *
     * @return array
     */
    protected function generateIndexes($table): array
    {
        $result  = [];
        $indexes = $table->getIndexes();
        foreach ($indexes as $index) {
            if ($index->isPrimary()) {
                continue;
            }
            $indexObject = new Index($index);
            $line        = $indexObject->generateAddMigration();
            $result[]    = $line.';';
        }

        return $result;
    }

    /**
     * @param Table $table
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return array|bool
     */
    protected function getAlterTableInfo($table)
    {
        $columns = $table->getColumns();
        $indexes = $table->getIndexes();
        $name    = $table->getName();

        $databaseService = new DatabaseService($this->config, $this->files);
        $upMigrations    = [
            'columns' => [
                'add'  => [],
                'drop' => [],
            ],
            'indexes' => [
                'add'  => [],
                'drop' => [],
            ],
        ];
        $downMigrations  = [
            'columns' => [
                'add'  => [],
                'drop' => [],
            ],
            'indexes' => [
                'add'  => [],
                'drop' => [],
            ],
        ];

        $updated = false;

        $currentColumns = $databaseService->getTableColumns($name);
        $newColumnNames = array_map(function ($column) {
            return $column->getName();
        }, $columns);
        $oldColumnNames = array_map(function ($column) {
            return $column->getName();
        }, $currentColumns);

        $addedColumns   = array_diff($newColumnNames, $oldColumnNames);
        $removedColumns = array_diff($oldColumnNames, $newColumnNames);

        $previousColumnName = '';
        foreach ($columns as $column) {
            if (in_array($column->getName(), $addedColumns)) {
                $columnObject = new Column($column);

                $upMigrations['columns']['add'][]    = $columnObject->generateAddMigration($previousColumnName).';';
                $downMigrations['columns']['drop'][] = $columnObject->generateDropMigration().';';
                $updated                             = true;
            }
            $previousColumnName = $column->getName();
        }

        $previousColumnName = '';
        foreach ($currentColumns as $column) {
            if (in_array($column->getName(), $removedColumns)) {
                $columnObject = new Column($column);

                $upMigrations['columns']['drop'][]  = $columnObject->generateDropMigration().';';
                $downMigrations['columns']['add'][] = $columnObject->generateAddMigration($previousColumnName).';';
                $updated                            = true;
            }
            $previousColumnName = $column->getName();
        }

        $currentIndexes = $databaseService->getTableIndexes($name);
        $newIndexNames  = array_map(function ($index) {
            return $index->getName();
        }, $indexes);
        $oldIndexNames  = array_map(function ($index) {
            return $index->getName();
        }, $currentIndexes);

        $addedIndexes   = array_diff($newIndexNames, $oldIndexNames);
        $removedIndexes = array_diff($oldIndexNames, $newIndexNames);

        foreach ($indexes as $index) {
            if (in_array($index->getName(), $addedIndexes)) {
                $indexObject = new Index($index);

                $upMigrations['indexes']['add'][]    = $indexObject->generateAddMigration().';';
                $downMigrations['indexes']['drop'][] = $indexObject->generateDropMigration().';';
                $updated                             = true;
            }
        }

        foreach ($currentIndexes as $index) {
            if (in_array($index->getName(), $removedIndexes)) {
                $indexObject = new Index($index);

                $upMigrations['indexes']['drop'][]  = $indexObject->generateDropMigration().';';
                $downMigrations['indexes']['add'][] = $indexObject->generateAddMigration().';';
                $updated                            = true;
            }
        }

        if (!$updated) {
            return false;
        }

        return [
            'upMigrations'   => $upMigrations,
            'downMigrations' => $downMigrations,
        ];
    }
}
