<?php
namespace LaravelRocket\Generator\Validators\Rules\Columns;

use LaravelRocket\Generator\Validators\Error;
use LaravelRocket\Generator\Validators\Rules\Base;

class GenderWithVarChar extends Base
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

        $errors = [];

        $type = $column->getType();
        if ($type === 'datetime') {
            $errors[] = new Error(
                'Must not use \'datetime\' type.',
                Error::LEVEL_ERROR,
                $table->getName().'/'.$column->getName(),
                'Use \'timestamp\' instead.'
            );
        }

        return $this->response($errors);
    }
}
