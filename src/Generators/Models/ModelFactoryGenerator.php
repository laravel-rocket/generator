<?php
namespace LaravelRocket\Generator\Generators\Models;

class ModelFactoryGenerator extends ModelBaseGenerator
{
    /**
     * @return string
     */
    protected function getPath(): string
    {
        $modelName = $this->getModelName();

        return database_path('/factories/'.$modelName.'Factory.php');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return 'model.factory';
    }

    /**
     * @return array
     */
    protected function getVariables(): array
    {
        $modelName                 = $this->getModelName();
        $variables                 = $this->getFillableColumns();
        $variables['modelName']    = $modelName;
        $variables['variableName'] = camel_case($modelName);

        return $variables;
    }

    protected function getFillableColumns()
    {
        $columnInfo = [
            'columns'         => [],
            'authenticatable' => false,
        ];

        $excludes = ['id', 'remember_token', 'created_at', 'deleted_at', 'updated_at'];

        foreach ($this->table->getColumns() as $column) {
            $name = $column->getName();
            $type = $column->getType();

            if (in_array($name, $excludes)) {
                continue;
            }

            if ($name == 'remember_token') {
                $columnInfo['authenticatable'] = true;
            }
            $value  = '';
            $fakers = [
                'name',
                'address',
                'latitude',
                'latitude',
                'country',
                'city',
                'phone_number',
                'company',
                'country_code',
                'language_code',
                'currency_code',
                'uuid',
            ];

            foreach ($fakers as $faker) {
                if (ends_with($name, $faker)) {
                    $value = '$faker->'.camel_case($faker);
                    break;
                }
            }
            if ($column->isNullable()) {
                $value = 'null';
            }
            if (empty($value)) {
                switch ($type) {
                    case 'varchar':
                        $value = "''";
                        break;
                    case 'text':
                    case 'mediumtext':
                    case 'longtext':
                        $value = '$faker->sentences(3)';
                        break;
                    case 'int':
                    case 'bigInt':
                    case 'decimal':
                        $value = '0';
                        break;
                    case 'timestamp':
                    case 'timestamp_f':
                        $value = '$faker->dateTime($max = \'now\')';
                        break;
                }
            }

            if (empty($value)) {
                $value = "''";
            }
            $columnInfo['columns'][$name] = $value;
        }

        return $columnInfo;
    }
}
