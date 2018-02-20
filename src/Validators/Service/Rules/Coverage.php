<?php
namespace LaravelRocket\Generator\Validators\Service\Rules;

use LaravelRocket\Generator\Validators\BaseRule;
use LaravelRocket\Generator\Validators\Error;

class Coverage extends BaseRule
{
    public function validate($data)
    {
        $name = array_get($data, 'name');

        /** @var \LaravelRocket\Generator\Objects\ClassLike $interface */
        $interface = array_get($data, 'interface');
        /** @var \LaravelRocket\Generator\Objects\ClassLike $class */
        $class = array_get($data, 'class');
        /** @var \LaravelRocket\Generator\Objects\ClassLike $test */
        $test = array_get($data, 'test');

        $errors = [];

        if (!file_exists($test->getPath())) {
            $errors[] = new Error(
                'TestFile  '.$test->getPath().' does\'t exists.',
                Error::LEVEL_ERROR,
                $name,
                'Add unit test file named : '.$test->getPath()
                );

            return $this->response($errors);
        }

        $testMethods = $test->getMethods();
        foreach ($interface->getMethods() as $name => $method) {
            $testName = 'test'.ucfirst($name);
            if (!array_key_exists($testName, $testMethods)) {
                $errors[] = new Error(
                    'Method '.$name.' has no test method ( '.$testName.').',
                    Error::LEVEL_ERROR,
                    $name,
                    'Write unit test named : '.$testName
                );
            }
        }

        return $this->response($errors);
    }
}
