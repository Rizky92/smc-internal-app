<?php

namespace App\Support\Mixins;

use Closure;

class CustomStringable
{
    /**
     * @return \Closure(): int
     */
    public function toInt(): Closure
    {
        /** 
         * @scope
         * @psalm-scope-this Illuminate\Support\Stringable
         */
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