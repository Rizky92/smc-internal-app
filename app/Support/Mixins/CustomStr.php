<?php

namespace App\Support\Mixins;

use Closure;

class CustomStr
{
    /**
     * @return \Closure(string): int
     */
    public function parseInt(): Closure
    {
        return fn (string $value): int => intval($value);
    }

    /**
     * @return \Closure(string): float
     */
    public function parseDouble(): Closure
    {
        return fn (string $value): float => floatval($value);
    }

    /**
     * @return \Closure(string): float
     */
    public function parseFloat(): Closure
    {
        return fn (string $value): float => floatval($value);
    }

    /**
     * @return \Closure(string): bool
     */
    public function parseBoolean(): Closure
    {
        return fn (string $value): bool => boolval($value);
    }
}