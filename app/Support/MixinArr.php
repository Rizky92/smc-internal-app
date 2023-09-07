<?php

namespace App\Support;

use Closure;

class MixinArr
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
