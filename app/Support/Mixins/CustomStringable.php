<?php

namespace App\Support\Mixins;

use Closure;
use Illuminate\Support\Stringable;

class CustomStringable
{
    /**
     * @return \Closure(string): \Illuminate\Support\Stringable
     */
    public function wrap()
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