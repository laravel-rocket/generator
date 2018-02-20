<?php
namespace LaravelRocket\Generator\Validators\Services;

use LaravelRocket\Generator\Objects\ClassLike;
use LaravelRocket\Generator\Validators\BaseValidator;
use LaravelRocket\Generator\Validators\Services\Rules\Consistency;
use LaravelRocket\Generator\Validators\Services\Rules\Coverage;

class ServiceValidator extends BaseValidator
{
    /**
     * @param \LaravelRocket\Generator\Objects\Definitions $json
     *
     * @return array
     */
    public function validate($json)
    {

        /** @var \LaravelRocket\Generator\Validators\BaseRule[] $tableRules */
        $rules = [
            Coverage::class,
            Consistency::class,
        ];

        $interfacePath = app_path('Services');
        $services      = $this->getDirectoryFiles($interfacePath, 'Interface.php');

        $roots    = [];
        foreach ($services as $service) {
            $root    = pathinfo($service, PATHINFO_FILENAME);
            $roots[] = substr($root, 0, strlen($root) - 9);
        }

        $success = true;
        $errors  = [];

        foreach ($roots as $root) {
            $interface = new ClassLike(app_path('Services'.DIRECTORY_SEPARATOR.$root.'Interface.php'));
            $class     = new ClassLike(app_path('Services'.DIRECTORY_SEPARATOR.'Production'.DIRECTORY_SEPARATOR.$root.'.php'));
            $test      = new ClassLike(base_path('tests'.DIRECTORY_SEPARATOR.'Services'.DIRECTORY_SEPARATOR.$root.'Test.php'));

            foreach ($rules as $rule) {
                list($ruleSuccess, $ruleErrors) = $rule->validate(
                    ['name' => $root, 'interface' => $interface, 'class' => $class, 'test' => $test]
                );
                if (!$ruleSuccess) {
                    $success = false;
                }
                $errors = array_merge($errors, $ruleErrors);
            }
        }

        return [$success, $errors];
    }
}
