<?php

namespace App\Support\Mixins;

use Closure;

class CustomArr
{
    /**
     * @return \Closure(array): bool
     */
    public function isList(): Closure
    {
        return function (array $values): bool {
            $keys = array_keys($values);

            return array_keys($keys) === $keys;
        };
    }
}