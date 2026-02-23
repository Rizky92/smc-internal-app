<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Fluent;

class MixinArr
{
    /**
     * @return Closure(array): bool
     */
    public function isList(): Closure
    {
        return function (array $values): bool {
            $keys = array_keys($values);

            return array_keys($keys) === $keys;
        };
    }

    /**
     * @return Closure(array): Fluent
     */
    public function fluent(): Closure
    {
        return fn (array $values): Fluent => new Fluent($values);
    }
}
