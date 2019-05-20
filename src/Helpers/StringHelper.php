<?php
namespace LaravelRocket\Generator\Helpers;

class StringHelper
{
    public static function hasPrefix(string $haystack, array $needles): bool
    {
        $elements    = explode('_', $haystack);
        $lastElement = $elements[count($elements) - 1];

        return in_array($lastElement, $needles);
    }
}
