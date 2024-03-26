<?php

namespace LaravelRocket\Generator\Validators\Services\Rules;

use Illuminate\Support\Arr;
use LaravelRocket\Generator\Validators\BaseRule;
use LaravelRocket\Generator\Validators\Error;

class Consistency extends BaseRule
{
    public function validate($data)
    {
        $name = Arr::get($data, 'name');

        /** @var \LaravelRocket\Generator\Objects\ClassLike $interface */
        $interface = Arr::get($data, 'interface');
        /** @var \LaravelRocket\Generator\Objects\ClassLike $class */
        $class = Arr::get($data, 'class');
        /** @var \LaravelRocket\Generator\Objects\ClassLike $test */
        $test = Arr::get($data, 'test');

        $errors = [];

        if (!file_exists($class->getPath())) {
            $errors[] = new Error(
                'Production Class '.$class->getPath().' does\'t exists.',
                Error::LEVEL_ERROR,
                $name,
                'Add class file named : '.$class->getPath()
            );

            return $this->response($errors);
        }

        $objectMethods    = array_keys($class->getMethods());
        $interfaceMethods = array_keys($interface->getMethods());

        $onlyInObjects = array_diff($objectMethods, $interfaceMethods);

        $reflection = $interface->getReflection();

        foreach ($onlyInObjects as $methodName) {
            $object = $class->getMethods()[$methodName];

            if ($object->isPublic()) {
                $reflectionMethod = $reflection->getMethod($methodName);
                if (empty($reflectionMethod)) {
                    $errors[] = new Error(
                        'Method '.$methodName.' doesn\'t exist in interface.',
                        Error::LEVEL_ERROR,
                        $name,
                        'Add it to  Interface file : '.$interface->getPath()
                    );
                }
            }
        }

        return $this->response($errors);
    }
}
