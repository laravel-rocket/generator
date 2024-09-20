<?php

namespace LaravelRocket\Generator\Validators;

class BaseRule
{
    public function validate($data)
    {
        return $this->response();
    }

    /**
     * @param \LaravelRocket\Generator\Validators\Error[] $errors
     *
     * @return array
     */
    public function response($errors = [])
    {
        if (!is_array($errors)) {
            $errors = [$errors];
        }
        $success = true;
        foreach ($errors as $error) {
            if ($error->getLevel() === Error::LEVEL_ERROR) {
                $success = false;
                break;
            }
        }

        return [$success, $errors];
    }
}
