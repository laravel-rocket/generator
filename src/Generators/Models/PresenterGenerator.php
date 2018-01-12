<?php
namespace LaravelRocket\Generator\Generators\Models;

class PresenterGenerator extends ModelBaseGenerator
{
    /**
     * @return string
     */
    protected function getPath(): string
    {
        $modelName = $this->getModelName();

        return app_path('/Presenters/'.$modelName.'.php');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return 'model.presenter';
    }

    /**
     * @return array
     */
    protected function getVariables(): array
    {
        $modelName                 = $this->getModelName();
        $variables                 = $this->getColumns();
        $variables['modelName']    = $modelName;
        $variables['variableName'] = camel_case($modelName);

        return $variables;
    }

    protected function getColumns()
    {
        $columnInfo = [
            'columns'            => [],
            'multilingualFields' => [],
            'imageColumns'       => [],
        ];

        foreach ($this->table->getColumns() as $column) {
            $name = $column->getName();
            $type = $column->getType();

            if (preg_match('/^(.+)_en$/', $name, $matches)) {
                $columnInfo['multilingualFields'][] = $matches[1];
            }
            if (preg_match('/^(.*image)_id$/', $name, $matches)) {
                $columnInfo['imageColumns'][] = $matches[1];
            }

            if (empty($value)) {
                switch ($type) {
                    case 'varchar':
                    case 'text':
                    case 'mediumText':
                    case 'longText':
                        $value = 'string';
                        break;
                    case 'int':
                    case 'bigInt':
                        $value = 'int';
                        break;
                    case 'decimal':
                        $value = 'double';
                        break;
                    case 'timestamp':
                        $value = '\Carbon\Carbon';
                        break;
                    default:
                        $value = 'mixed';
                        break;
                }
            }

            $columnInfo['columns'][$name] = $value;
        }

        return $columnInfo;
    }
}
