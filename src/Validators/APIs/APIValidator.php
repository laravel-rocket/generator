<?php

namespace LaravelRocket\Generator\Validators\APIs;

use LaravelRocket\Generator\Validators\BaseValidator;

class APIValidator extends BaseValidator
{
    /**
     * @param \LaravelRocket\Generator\Objects\OpenAPI\OpenAPISpec $spec
     * @param \LaravelRocket\Generator\Objects\Definitions         $json
     *
     * @return array
     */
    public function validate($spec, $json)
    {
        $success = true;
        $errors  = [];

        return [$success, $errors];
    }
}
