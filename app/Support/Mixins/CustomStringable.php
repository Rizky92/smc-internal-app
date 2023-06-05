<?php

namespace App\Support\Mixins;

use Closure;

/**
 * @psalm-scope-this \Illuminate\Support\Stringable
 */
class CustomStringable
{
    /**
     * @return \Closure(): int
     */
    public function toInt(): Closure
    {
        return fn (): int => intval($this->value);
    }

    public function toDouble(): Closure
    {
        return fn (): float => floatval($this->value);
    }

    public function toFloat(): Closure
    {
        return fn (): float => floatval($this->value);
    }

    public function toBoolean(): Closure
    {
        return fn (): bool => boolval($this->value);
    }
}