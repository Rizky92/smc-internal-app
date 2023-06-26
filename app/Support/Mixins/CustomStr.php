<?php

namespace App\Support\Mixins;

use Closure;

class CustomStr
{
    /** 
     * @return \Closure(string, string, ?string): string
     */
    public function wrap(): Closure
    {
        return function (string $value, string $startsWith, ?string $endsWith = null) {
            if (! $endsWith) {
                return $startsWith . $value . $startsWith;
            }

            return $startsWith . $value . $endsWith;
        };
    }

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