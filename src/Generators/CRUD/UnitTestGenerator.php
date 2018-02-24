<?php
namespace LaravelRocket\Generator\Generators\CRUD;

class UnitTestGenerator extends CRUDBaseGenerator
{
    /**
     * @return array
     */
    protected function getVariables(): array
    {
        $modelName                 = $this->getModelName();
        $variables                 = $this->getFillableColumns();
        $variables['modelName']    = $modelName;
        $variables['variableName'] = camel_case($modelName);
        $variables['className']    = $modelName.'Repository';
        $variables['tableName']    = $this->table->getName();

        $found = false;
        foreach ($this->table->getColumns() as $column) {
            if (in_array($column->getName(), ['remember_token', 'id', 'deleted_at', 'created_at', 'updated_at'])) {
                continue;
            }
            if (in_array($column->getType(), ['varchar', 'text', 'mediumtext', 'longtext'])) {
                $variables['testColumnName'] = $column->getName();
                $variables['testData']       = 'str_random(10)';
                $found                       = true;
                break;
            }
        }

        if (!$found) {
            foreach ($this->table->getColumns() as $column) {
                if (in_array($column->getName(), ['remember_token', 'id', 'deleted_at', 'created_at', 'updated_at'])) {
                    continue;
                }
                if (in_array($column->getType(), ['int', 'bigint', 'decimal'])) {
                    $variables['testColumnName'] = $column->getName();
                    $variables['testData']       = 'rand(50,100)';
                    break;
                }
            }
        }

        if (!$found) {
            foreach ($this->table->getColumns() as $column) {
                if (in_array($column->getName(), ['remember_token', 'id', 'deleted_at', 'created_at', 'updated_at'])) {
                    continue;
                }
                $variables['testColumnName'] = $column->getName();
                $variables['testData']       = 'rand(50,100)';
                break;
            }
        }

        return $variables;
    }

    protected function getFillableColumns()
    {
        $columnInfo = [
            'fillableColumns'      => [],
            'timestampColumns'     => [],
            'unixTimestampColumns' => [],
            'booleanColumns'       => [],
        ];

        $excludes = ['id', 'remember_token', 'created_at', 'deleted_at', 'updated_at'];

        foreach ($this->table->getColumns() as $column) {
            $name = $column->getName();
            $type = $column->getType();

            if (in_array($name, $excludes)) {
                continue;
            }
            if (ends_with($name, '_at') && ($type === 'timestamp' || $type === 'timestamp_f')) {
                $columnInfo['timestampColumns'][] = $name;
                continue;
            }
            if (ends_with($name, '_at') && $type === 'int') {
                $columnInfo['unixTimestampColumns'][] = $name;
                continue;
            }
            if ((starts_with($name, 'is_') || starts_with($name, 'has_')) && $type === 'int') {
                $columnInfo['booleanColumns'][] = $name;
                continue;
            }
            $columnInfo['fillableColumns'][] = $name;
        }

        return $columnInfo;
    }
}
