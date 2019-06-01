<?php
namespace LaravelRocket\Generator\Helpers;

use Illuminate\Support\Str;

class StringHelper
{
    /**
     * @param string       $haystack
     * @param array|string $needles
     *
     * @return bool
     */
    public static function hasPrefix(string $haystack, $needles): bool
    {
        if (!is_array($needles)) {
            $needles = [$needles];
        }

        $elements    = explode('_', $haystack);
        $lastElement = $elements[count($elements) - 1];

        return in_array($lastElement, $needles);
    }

    /**
     * @param string       $haystack
     * @param array|string $needles
     *
     * @return bool
     */
    public static function hasPostFix(string $haystack, $needles): bool
    {
        if (!is_array($needles)) {
            $needles = [$needles];
        }

        foreach ($needles as $needle) {
            if ($haystack === $needle || Str::endsWith($haystack, ucfirst($needle))) {
                return true;
            }
        }

        return false;
    }
}
