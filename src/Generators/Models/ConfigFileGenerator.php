<?php
namespace LaravelRocket\Generator\Generators\Models;

use Illuminate\Support\Arr;

class ConfigFileGenerator extends ModelBaseGenerator
{
    /**
     * @return string
     */
    protected function getPath(): string
    {
        $tableName = $this->table->getName();

        return config_path('data/tables/'.$tableName.'/columns.php');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return 'model.config';
    }

    /**
     * @return array
     */
    protected function getVariables(): array
    {
        $variables              = $this->getColumns();
        $variables['tableName'] = $this->table->getName();

        return $variables;
    }

    /**
     * @return array
     */
    protected function getColumns(): array
    {
        $columnInfo = [
            'columns' => [],
        ];

        foreach ($this->table->getColumns() as $column) {
            $columnDefinition = $this->json->getColumnDefinition($this->table->getName(), $column->getName());

            $name = $column->getName();
            $type = Arr::get($columnDefinition, 'type', '');

            $columnInfo['columns'][$name] = [
                'options' => [],
            ];

            switch ($type) {
                case 'type':
                    $options = Arr::get($columnDefinition, 'options', []);
                    foreach ($options as $index => $option) {
                        $optionValue                                           = Arr::get($option, 'value', $index);
                        $optionName                                            = Arr::get($option, 'name', $index);
                        $columnInfo['columns'][$name]['options'][$optionValue] = $optionName;
                    }
                    break;
            }
        }

        return $columnInfo;
    }
}
