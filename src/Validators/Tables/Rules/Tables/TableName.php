<?php
namespace LaravelRocket\Generator\Validators\Tables\Rules\Tables;

use function ICanBoogie\pluralize;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LaravelRocket\Generator\Validators\BaseRule;
use LaravelRocket\Generator\Validators\Error;

class TableName extends BaseRule
{
    public function validate($data)
    {
        /** @var \TakaakiMizuno\MWBParser\Elements\Table $table */
        $table = Arr::get($data, 'table', null);
        if (empty($table)) {
            return $this->response(new Error('No table passed.', Error::LEVEL_ERROR, 'System'));
        }

        $errors = [];

        $name = $table->getName();
        if (preg_match('/[^a-z0-9_]/', $name, $matches)) {
            $errors[] = new Error(
                sprintf(
                    'Only a to z ( small letters ) and numbers and underscore(_) can be used for table name. %s found.',
                    $matches[0]
                ),
                Error::LEVEL_ERROR,
                $name,
                ''
            );
        }

        $idealName = pluralize(Str::snake($name));

        if (Str::snake($name) != $name) {
            $errors[] = new Error(
                'Table name must be snake case.',
                Error::LEVEL_ERROR,
                $name,
                "User $idealName"
            );
        }

        if (pluralize($name) != $name) {
            $errors[] = new Error(
                'Table name must be plural form.',
                Error::LEVEL_ERROR,
                $name,
                "User $idealName"
            );

            $errors[] = sprintf('Table name must be plural form : %s', $name);
        }

        return $this->response($errors);
    }
}
