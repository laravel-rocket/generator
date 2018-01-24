<?php
namespace LaravelRocket\Generator\Validators;

use LaravelRocket\Generator\Validators\Rules\Columns\AvoidDateTime;
use LaravelRocket\Generator\Validators\Rules\Columns\AvoidLongVarChar;
use LaravelRocket\Generator\Validators\Rules\Columns\ColumnName;
use LaravelRocket\Generator\Validators\Rules\Columns\GenderWithVarChar;
use LaravelRocket\Generator\Validators\Rules\Tables\PrimaryKeyName;
use LaravelRocket\Generator\Validators\Rules\Tables\TableName;

class TableSchemaValidator extends BaseValidator
{
    /**
     * @param \TakaakiMizuno\MWBParser\Elements\Table[]    $tables
     * @param \LaravelRocket\Generator\Objects\Definitions $json
     *
     * @return array
     */
    public function validate($tables, $json)
    {

        /** @var \LaravelRocket\Generator\Validators\Rules\Base[] $tableRules */
        $tableRules = [
            new TableName(),
            new PrimaryKeyName(),
        ];

        /** @var \LaravelRocket\Generator\Validators\Rules\Base[] $columnRules */
        $columnRules = [
            new ColumnName(),
            new AvoidLongVarChar(),
            new AvoidDateTime(),
            new GenderWithVarChar(),
        ];

        $success = true;
        $errors  = [];

        foreach ($tables as $table) {
            foreach ($tableRules as $rule) {
                list($ruleSuccess, $ruleErrors) = $rule->validate(['table' => $table]);
                if (!$ruleSuccess) {
                    $success = false;
                }
                $errors = array_merge($errors, $ruleErrors);
            }

            foreach ($table->getColumns() as $column) {
                foreach ($columnRules as $rule) {
                    list($ruleSuccess, $ruleErrors) = $rule->validate(['table' => $table, 'column' => $column]);
                    if (!$ruleSuccess) {
                        $success = false;
                    }
                    $errors = array_merge($errors, $ruleErrors);
                }
            }
        }

        return [$success, $errors];
    }
}
