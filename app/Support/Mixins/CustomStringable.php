<?php

namespace App\Support\Mixins;

use Closure;
use Illuminate\Support\Stringable;

/**
 * @property-read string $value
 */
class CustomStringable
{
    /**
     * @return \Closure(): string
     */
    public function value(): Closure
    {
        /**
         * @psalm-scope-this Illuminate\Support\Stringable
         */
        return fn (): string => $this->value;
    }

    /**
     * @return \Closure(string): \Illuminate\Support\Stringable
     */
    public function wrap(): Closure
    {
        /** 
         * @psalm-scope-this Illuminate\Support\Stringable
         */
        return fn (string $with): Stringable => new Stringable($with . $this->value . $with);
    }

    /**
     * @return \Closure(): int
     */
    public function toInt(): Closure
    {
        /** 
         * @psalm-scope-this Illuminate\Support\Stringable
         */
        return fn (): int => intval($this->value);
    }

    public function toDouble(): Closure
    {
        /** 
         * @psalm-scope-this Illuminate\Support\Stringable
         */
        return fn (): float => floatval($this->value);
    }

    public function toFloat(): Closure
    {
        /** 
         * @psalm-scope-this Illuminate\Support\Stringable
         */
        return fn (): float => floatval($this->value);
    }

    public function toBoolean(): Closure
    {
        /** 
         * @psalm-scope-this Illuminate\Support\Stringable
         */
        return fn (): bool => boolval($this->value);
    }
}