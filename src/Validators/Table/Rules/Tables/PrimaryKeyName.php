<?php
namespace LaravelRocket\Generator\Validators\Table\Rules\Tables;

use LaravelRocket\Generator\Validators\BaseRule;
use LaravelRocket\Generator\Validators\Error;

class PrimaryKeyName extends BaseRule
{
    public function validate($data)
    {
        /** @var \TakaakiMizuno\MWBParser\Elements\Table $table */
        $table = array_get($data, 'table', null);
        if (empty($table)) {
            return $this->response(new Error('No table passed.', Error::LEVEL_ERROR, 'System'));
        }

        $errors = [];

        $indexes       = $table->getIndexes();
        $hasPrimaryKey = false;

        foreach ($indexes as $index) {
            if (!$index->isPrimary()) {
                continue;
            }

            $hasPrimaryKey = true;
            $columns       = $index->getColumns();

            if (count($columns) > 1) {
                $errors[] = new Error(
                    'Cannot use compound primary key.',
                    Error::LEVEL_ERROR,
                    $table->getName(),
                    "Use column 'id' for primary key"
                );
                continue;
            }

            $column = $columns[0];
            if ($column->getName() !== 'id') {
                $errors[] = new Error(
                    'Primary key column must be named \'id\'',
                    Error::LEVEL_ERROR,
                    $table->getName(),
                    "Use column 'id' for primary key"
                );
            }

            if ($column->getType() !== 'bigint') {
                $errors[] = new Error(
                    'Primary key column type must be \'bigint\'',
                    Error::LEVEL_ERROR,
                    $table->getName(),
                    'Change column type to bigint.'
                );
            }
        }

        if (!$hasPrimaryKey) {
            $errors[] = new Error(
                'Table must have primary key ',
                Error::LEVEL_ERROR,
                $table->getName(),
                'Change column type to bigint.'
            );
        }

        return $this->response($errors);
    }
}
