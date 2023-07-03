<?php

namespace App\Support\Mixins;

use Closure;
use Illuminate\Support\Stringable;

class CustomStringable
{
    /**
     * Returns the underlying value
     * 
     * @return \Closure(): string
     */
    public function value(): Closure
    {
        /** @psalm-scope-this Illuminate\Support\Stringable */
        return fn (): string => $this->value;
    }

    /**
     * Wrap the underlying string with given values
     * 
     * @return \Closure(string, ?string): \Illuminate\Support\Stringable
     */
    public function wrap(): Closure
    {
        /** @psalm-scope-this Illuminate\Support\Stringable */
        return fn (string $startsWith, ?string $endsWith = null) =>
            is_null($endsWith)
                ? new Stringable($startsWith .  $this->value . $startsWith)
                : new Stringable($startsWith . $this->value . $endsWith);
    }

    /**
     * Returns the underlying value in type integer
     * 
     * @return \Closure(): int
     */
    public function toInt(): Closure
    {
        /** @psalm-scope-this Illuminate\Support\Stringable */
        return fn (): int => intval($this->value);
    }

    /**
     * Alias of toFloat()
     * Returns the underlying value in type float
     * 
     * @return \Closure(): float
     */
    public function toDouble(): Closure
    {
        /** @psalm-scope-this Illuminate\Support\Stringable */
        return fn (): float => floatval($this->value);
    }

    /**
     * Returns the underlying value in type float
     * 
     * @return \Closure(): float
     */
    public function toFloat(): Closure
    {
        /** @psalm-scope-this Illuminate\Support\Stringable */
        return fn (): float => floatval($this->value);
    }


    /**
     * Returns the underlying value in type boolean
     * 
     * @return \Closure(): bool
     */
    public function toBoolean(): Closure
    {
        /** @psalm-scope-this Illuminate\Support\Stringable */
        return fn (): bool => boolval($this->value);
    }
}