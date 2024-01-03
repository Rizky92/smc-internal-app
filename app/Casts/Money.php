<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class Money implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  Model  $model
     * @param  int|float|mixed  $value
     * @return mixed
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return $value / 100;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  Model  $model
     * @param  int|float|mixed  $value
     * @return mixed
     */
    public function set($model, string $key, $value, array $attributes)
    {
        return $value * 100;
    }
}
