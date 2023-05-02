<?php

namespace App\Support\Mixins;

/**
 * @extends \Illuminate\Support\Stringable
 */
class CustomStringable
{
    public function toInt()
    {
        return fn (): int => intval($this->value);
    }

    public function toDouble()
    {
        return fn (): float => floatval($this->value);
    }

    public function toFloat()
    {
        return fn (): float => floatval($this->value);
    }

    public function toBoolean()
    {
        return fn (): bool => boolval($this->value);
    }
}