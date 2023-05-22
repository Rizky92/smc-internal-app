<?php

namespace App\Support\Mixins;

use Closure;

class CustomStr
{
    public function parseInt(): Closure
    {
        return fn (string $value): int => intval($value);
    }

    public function parseDouble(): Closure
    {
        return fn (string $value): float => floatval($value);
    }

    public function parseFloat(): Closure
    {
        return fn (string $value): float => floatval($value);
    }

    public function parseBoolean(): Closure
    {
        return fn (string $value): bool => boolval($value);
    }
}