<?php
namespace LaravelRocket\Generator\Validators\Tables\Rules\Columns;

use Illuminate\Support\Arr;
use LaravelRocket\Generator\Objects\Column;
use LaravelRocket\Generator\Validators\BaseRule;
use LaravelRocket\Generator\Validators\Error;

class OptionDefined extends BaseRule
{
    public function validate($data)
    {
        /** @var \TakaakiMizuno\MWBParser\Elements\Table $table */
        $table = Arr::get($data, 'table', null);
        if (empty($table)) {
            return $this->response(new Error('No table passed.', Error::LEVEL_ERROR, 'System'));
        }

        /** @var \TakaakiMizuno\MWBParser\Elements\Column $column */
        $column = Arr::get($data, 'column', null);
        if (empty($column)) {
            return $this->response(new Error('No column passed.', Error::LEVEL_ERROR, 'System'));
        }

        /** @var array */
        $definition = Arr::get($data, 'definition', []);
        if (empty($column)) {
            return $this->response(new Error('No column passed.', Error::LEVEL_ERROR, 'System'));
        }

        $errors   = [];

        $columnObject = new Column($column, $table, $definition);
        if ($columnObject->hasOptionConfiguration()) {
            if (!is_array(Arr::get($definition, 'options'))) {
                $errors[] = new Error(
                    sprintf(
                        'Need to have option list in configuration file.'
                    ),
                    Error::LEVEL_ERROR,
                    $table->getName().'/'.$column->getName(),
                    'Update config json file and add option list to the column configuration'
                );
            }
        }

        return $this->response($errors);
    }
}
