<?php

namespace App\Support\Mixins;

class CustomStr
{
    public function parseInt()
    {
        return fn ($value) => intval($value);
    }

    public function parseDouble()
    {
        return fn ($value) => floatval($value);
    }

    public function parseFloat()
    {
        return fn ($value) => floatval($value);
    }

    public function parseBoolean()
    {
        return fn ($value) => boolval($value);
    }
}