<?php
namespace LaravelRocket\Generator\Generators\Models;

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
            $type = array_get($columnDefinition, 'type', '');

            $columnInfo['columns'][$name] = [
                'options' => [],
            ];

            switch ($type) {
                case 'type':
                    $options = array_get($columnDefinition, 'options', []);
                    foreach ($options as $index => $option) {
                        $value                                           = array_get($option, 'value', $index);
                        $name                                            = array_get($option, 'name', $index);
                        $columnInfo['columns'][$name]['options'][$value] = $name;
                    }
                    break;
            }
        }

        return $columnInfo;
    }
}
