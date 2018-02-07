<?php
namespace LaravelRocket\Generator\Generators\Models;

use LaravelRocket\Generator\Objects\Column;
use function ICanBoogie\pluralize;

class LanguageFileGenerator extends ModelBaseGenerator
{
    /**
     * @return string
     */
    protected function getPath(): string
    {
        $modelName = $this->getModelName();
        $viewName  = kebab_case(pluralize($modelName));

        return resource_path('lang/en/tables/'.$viewName.'/columns.php');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return 'model.language';
    }

    /**
     * @return array
     */
    protected function getVariables(): array
    {
        $modelName              = $this->getModelName();
        $variables              = $this->getColumns();
        $variables['modelName'] = $modelName;

        return $variables;
    }

    /**
     * @return array
     */
    protected function getColumns(): array
    {
        $columnInfo = [
            'columns'  => [],
            'booleans' => [],
            'options'  => [],
        ];

        foreach ($this->table->getColumns() as $column) {
            $columnDefinition = $this->json->getColumnDefinition($this->table->getName(), $column->getName());

            $name = $column->getName();

            $viewName = array_get($columnDefinition, 'name', '');
            if (empty($viewName)) {
                $viewName = $name;
                $viewName = preg_replace('/_id$/', ' ', $viewName);
                $viewName = title_case(preg_replace('/_/', ' ', $viewName));
            }

            $columnInfo['columns'][$name] = trim($viewName);

            $columnObject = new Column($column);

            list($type, $options) = $columnObject->getEditFieldType([], $columnDefinition);

            switch ($type) {
                case 'boolean':
                    if (empty($options) || count($options) === 0) {
                        if ($name === 'is_enabled') {
                            $columnInfo['booleans'][$name.'_true']  = 'Enabled';
                            $columnInfo['booleans'][$name.'_false'] = 'Disabled';
                        } else {
                            $columnInfo['booleans'][$name.'_true']  = 'Yes';
                            $columnInfo['booleans'][$name.'_false'] = 'No';
                        }
                    } else {
                        foreach ($options as $option) {
                            $columnInfo['booleans'][$name.'_'.array_get($option, 'value', '')] = array_get($option, 'name', '');
                        }
                    }
                    break;
                case 'select':
                    if (!empty($options) && count($options) !== 0) {
                        $columnInfo['options'][$name] = [];
                        foreach ($options as $option) {
                            $columnInfo['options'][$name][array_get($option, 'value', '')] = array_get($option, 'name', '');
                        }
                    }
                    break;
            }
        }

        return $columnInfo;
    }
}
