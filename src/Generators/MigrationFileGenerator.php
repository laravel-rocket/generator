<?php
namespace LaravelRocket\Generator\Generators;

use LaravelRocket\Generator\Services\FileService;
use TakaakiMizuno\MWBParser\Elements\Table;

class MigrationFileGenerator extends BaseGenerator
{
    /**
     * @param Table $table
     */
    public function generate($table)
    {
        $existingPath = $this->findExistingCreateMigrationFile($table);
        if (!empty($existingPath)) {
            unlink($existingPath);
        }

        $result              = $this->generateColumns($table);
        $result['tableName'] = $table->getName();
        $result['indexes']   = $this->generateIndexes($table);
        $result['className'] = $this->getClassName($table->getName());

        $filePath = $this->getPath($table->getName());
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

    protected function getClassName($name): string
    {
        return 'Create'.ucfirst(camel_case($name)).'Table';
    }

    protected function getPath($name)
    {
        $basePath = $this->getMigrationBasePath();

        return $basePath.DIRECTORY_SEPARATOR.date('Y_m_d_His').'_create_'.$name.'_table.php';
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
                    $type = 'mediumText';
                    break;
                case 'longtext':
                    $type = 'longText';
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
