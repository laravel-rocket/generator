<?php
namespace LaravelRocket\Generator\Generators\Models;

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

        return resource_path('/lang/en/tables/'.$viewName.'/columns.php');
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
        ];

        foreach ($this->table->getColumns() as $column) {
            $name     = $column->getName();
            $viewName = $name;
            $viewName = preg_replace('/_id$/', ' ', $viewName);
            $viewName = title_case(preg_replace('/_/', ' ', $viewName));

            $columnInfo['columns'][$name] = $viewName;

            if ($column->getType() === 'tinyint') {
                if ($name === 'is_enabled') {
                    $columnInfo['booleans'][$name.'_true']  = 'Enabled';
                    $columnInfo['booleans'][$name.'_false'] = 'Disabled';
                } else {
                    $columnInfo['booleans'][$name.'_true']  = 'Yes';
                    $columnInfo['booleans'][$name.'_false'] = 'No';
                }
            }
        }

        return $columnInfo;
    }
}
