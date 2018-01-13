<?php
namespace LaravelRocket\Generator\Generators;

use Carbon\Carbon;
use LaravelRocket\Generator\Services\FileService;
use TakaakiMizuno\MWBParser\Elements\Table;

class MigrationFileGenerator extends BaseGenerator
{
    /**
     * @param Table $table
     */
    public function generate($table)
    {
        $dateTime = Carbon::now();

        $existingPath = $this->findExistingCreateMigrationFile($table);
        if (!empty($existingPath)) {
            $filePath = $existingPath;
        } else {
            $filePath = $this->getPath($table->getName(), $dateTime);
        }

        $result              = $this->generateColumns($table);
        $result['tableName'] = $table->getName();
        $result['indexes']   = $this->generateIndexes($table);
        $result['className'] = $this->getClassName($table->getName(), $dateTime);

        /* @var FileService $fileService */
        $this->fileService->render('migration.create', $filePath, $result, true);
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
     *
     * @return string
     */
    protected function getClassName($name, $dateTime): string
    {
        return 'Create'.ucfirst(camel_case($name)).'Table';
    }

    /**
     * @param string $name
     * @param Carbon $dateTime
     *
     * @return string
     */
    protected function getPath($name, $dateTime)
    {
        $basePath = $this->getMigrationBasePath();

        return $basePath.DIRECTORY_SEPARATOR.$dateTime->format('Y_m_d_His').'_create_'.$name.'_table.php';
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
            if (($column->getName() === 'created_at' || $column->getName() === 'created_at') and $column->getType() === 'timestamp') {
                continue;
            }
            if ($column->getName() === 'deleted_at' and $column->getType() === 'timestamp') {
                $result['hasSoftDelete'] = true;
                continue;
            }
            if ($column->getName() === 'remember_token' and $column->getType() === 'varchar') {
                $result['hasRememberToken'] = true;
                continue;
            }
            $postfix = '';
            switch ($column->getType()) {
                case 'tinyint':
                    $type = 'boolean';
                    break;
                case 'bigint':
                    $type = $column->isUnsigned() ? 'unsignedBigInteger' : 'bigInteger';
                    break;
                case 'int':
                    $type = $column->isUnsigned() ? 'unsignedInteger' : 'integer';
                    break;
                case 'timestamp':
                    $type = 'timestamp';
                    break;
                case 'date':
                    $type = 'date';
                    break;
                case 'varchar':
                    $type = 'string';
                    if ($column->getLength() != 255) {
                        $postfix = ', '.$column->getLength();
                    }
                    break;
                case 'text':
                    $type = 'text';
                    break;
                case 'mediumtext':
                    $type = 'mediumtext';
                    break;
                case 'longtext':
                    $type = 'longtext';
                    break;
                case 'decimal':
                    $type    = 'decimal';
                    $postfix = ', '.$column->getPrecision().', '.$column->getScale();
                    break;
                default:
                    $type = 'unknown';
            }
            $line = '$table->'.$type.'(\''.$column->getName().$postfix.'\')';

            if ($column->isNullable()) {
                $line .= '->nullable()';
            }
            if (!is_null($column->getDefaultValue())) {
                switch ($column->getType()) {
                    case 'tinyint':
                        $defaultValue = (int) $column->getDefaultValue() == 1 ? 'true' : 'false';
                        $line .= '->default('.$defaultValue.')';
                        break;
                    case 'bigint':
                    case 'int':
                        $line .= '->default('.((int) $column->getDefaultValue()).')';
                        break;
                    case 'timestamp':
                    case 'date':
                    case 'varchar':
                    case 'text':
                    case 'mediumtext':
                    case 'longtext':
                        $line .= '->default(\''.($column->getDefaultValue()).'\')';
                        break;
                    case 'decimal':
                        $line .= '->default('.((float) $column->getDefaultValue()).')';
                        break;
                    default:
                        $line .= '->default(\''.($column->getDefaultValue()).'\')';
                        break;
                }
            }
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
            $type     = $index->isUnique() ? 'unique' : 'index';
            $names    = array_map(function ($column) {
                return $column->getName();
            }, $index->getColumns());
            $columns  = '\''.implode('\',\'', $names).'\'';
            $result[] = '$table->'.$type.'(['.$columns.'], \''.$index->getName().'\');';
        }

        return $result;
    }
}
