<?php
namespace LaravelRocket\Generator\Validators\Table\Rules\Columns;

use LaravelRocket\Generator\Validators\BaseRule;
use LaravelRocket\Generator\Validators\Error;

class ColumnName extends BaseRule
{
    public function validate($data)
    {
        /** @var \TakaakiMizuno\MWBParser\Elements\Table $table */
        $table = array_get($data, 'table', null);
        if (empty($table)) {
            return $this->response(new Error('No table passed.', Error::LEVEL_ERROR, 'System'));
        }

        /** @var \TakaakiMizuno\MWBParser\Elements\Column $column */
        $column = array_get($data, 'column', null);
        if (empty($column)) {
            return $this->response(new Error('No column passed.', Error::LEVEL_ERROR, 'System'));
        }

        $errors   = [];

        $name = $column->getName();
        if (preg_match('/[^a-z0-9_]/', $name, $matches)) {
            $errors[] = new Error(
                sprintf(
                    'Only a to z ( small letters ) and numbers and underscore(_) can be used for column name. %s found.',
                    $matches[0]
                ),
                Error::LEVEL_ERROR,
                $table->getName().'/'.$column->getName(),
                ''
            );
        }

        $idealName = snake_case($name);

        if (snake_case($name) != $name) {
            $errors[] = new Error(
                'Column name must be snake case.',
                Error::LEVEL_ERROR,
                $table->getName().'/'.$column->getName(),
                "User $idealName"
            );
        }

        return $this->response($errors);
    }
}
