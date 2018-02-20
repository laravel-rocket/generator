<?php
namespace LaravelRocket\Generator\Validators\API;

use LaravelRocket\Generator\Validators\BaseValidator;

class APIValidator extends BaseValidator
{
    /**
     * @param array                                        $oas
     * @param \LaravelRocket\Generator\Objects\Definitions $json
     *
     * @return array
     */
    public function validate($oas, $json)
    {
        return [];
    }
}
